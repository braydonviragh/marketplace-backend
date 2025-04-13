<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use App\Models\Rental;
use App\Models\Offer;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Http\Requests\PaymentRequest;
use App\Services\StripeService;
use App\Models\RentalStatus;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    use ApiResponse;
    
    protected PaymentService $paymentService;
    protected StripeService $stripeService;

    public function __construct(
        PaymentService $paymentService,
        StripeService $stripeService
    ) {
        $this->paymentService = $paymentService;
        $this->stripeService = $stripeService;
    }

    public function index(PaymentRequest $request)
    {
        $payments = $this->paymentService->getPayments(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new PaymentCollection($payments),
            'Payments retrieved successfully'
        );
    }

    public function show(int $id)
    {
        $payment = $this->paymentService->findPayment($id);
        
        return $this->resourceResponse(
            new PaymentResource($payment->load(['payer', 'payee', 'rental'])),
            'Payment retrieved successfully'
        );
    }

    /**
     * Create a new payment for a rental - initiates the escrow payment process
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'offer_id' => 'required|exists:offers,id',
            'payment_method' => 'required|string|in:stripe,apple_pay,paypal',
            'payment_method_id' => 'required_if:payment_method,stripe|string'
        ]);

        $user = Auth::user();
        $offerId = $validated['offer_id'];
        $paymentMethod = $validated['payment_method'];
        $paymentMethodId = $validated['payment_method_id'] ?? null;

        // Begin transaction
        DB::beginTransaction();
        try {
            // Get the offer with related data
            $offer = Offer::with(['product', 'product.user', 'product.user.stripeAccount'])
                ->where('id', $offerId)
                ->where('status', 'accepted')
                ->firstOrFail();

            // Verify the authenticated user is the renter
            if ($user->id !== $offer->user_id) {
                return $this->errorResponse('You are not authorized to make this payment', 403);
            }

            // Verify the product owner has a Stripe account
            if (!$offer->product->user->stripeAccount) {
                return $this->errorResponse('The product owner does not have a payment account set up', 400);
            }
            
            // Calculate platform fee (20%) and owner amount (80%)
            $totalAmount = $offer->price;
            $platformFee = $totalAmount * 0.20;
            $ownerAmount = $totalAmount - $platformFee;

            // Create the rental record
            $rental = Rental::create([
                'owner_id' => $offer->product->user_id,
                'renter_id' => $user->id,
                'offer_id' => $offer->id,
                'product_id' => $offer->product_id,
                'start_date' => $offer->start_date,
                'end_date' => $offer->end_date,
                'total_price' => $offer->price,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'rental_status_id' => RentalStatus::where('slug', 'pending')->first()->id,
            ]);

            // Create payment record
            $payment = Payment::create([
                'rental_id' => $rental->id,
                'payer_id' => $user->id,
                'payee_id' => $offer->product->user_id,
                'amount' => $totalAmount,
                'platform_fee' => $platformFee,
                'owner_amount' => $ownerAmount,
                'currency' => 'usd',
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'payment_details' => [
                    'type' => 'rental_payment',
                    'fee_split' => [
                        'platform_percentage' => 20,
                        'owner_percentage' => 80
                    ],
                    'release_date' => $offer->start_date
                ]
            ]);

            // Process payment with Stripe using 80/20 split
            $paymentData = $this->stripeService->processRentalPayment($rental, $paymentMethodId);
            
            if (!$paymentData['success']) {
                DB::rollBack();
                return $this->errorResponse('Failed to create payment: ' . ($paymentData['error'] ?? 'Unknown error'), 500);
            }

            // Update payment with Stripe info
            $payment->update([
                'stripe_payment_intent_id' => $paymentData['payment_intent_id'],
                'payment_details' => array_merge($payment->payment_details ?? [], [
                    'client_secret' => $paymentData['client_secret'] ?? null,
                    'platform_fee' => $paymentData['platform_fee'],
                    'owner_amount' => $paymentData['owner_amount']
                ])
            ]);

            // If payment succeeded immediately (possible with saved payment methods)
            if (isset($paymentData['status']) && $paymentData['status'] === 'succeeded') {
                $payment->update(['status' => 'completed']);
                
                // Create transaction record for the owner
                $this->createTransactionRecord(
                    $rental,
                    $offer->product->user_id,
                    $ownerAmount,
                    'credit',
                    'Rental payment received (80%)'
                );
                
                // Create transaction record for platform fee
                $this->createTransactionRecord(
                    $rental,
                    null, // Platform account
                    $platformFee,
                    'credit',
                    'Platform fee from rental (20%)'
                );
            }

            // Commit the transaction
            DB::commit();

            return $this->successResponse([
                'payment' => new PaymentResource($payment),
                'client_secret' => $paymentData['client_secret'] ?? null
            ], 'Payment processed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'offer_id' => $offerId,
                'exception' => $e
            ]);
            return $this->errorResponse('Failed to process payment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Confirm a payment after frontend processing
     */
    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_intent_id' => 'required|string'
        ]);

        try {
            $payment = Payment::where('stripe_payment_intent_id', $data['payment_intent_id'])->firstOrFail();
            
            // Verify payment intent status with Stripe
            $intent = \Stripe\PaymentIntent::retrieve($data['payment_intent_id']);
            
            if ($intent->status === 'succeeded') {
                // Update payment status
                $payment->update(['status' => 'completed']);
                
                // Update rental status to pending confirmation
                $payment->rental->update([
                    'rental_status_id' => RentalStatus::where('slug', 'pending')->first()->id
                ]);
                
                return $this->successResponse([
                    'payment' => new PaymentResource($payment),
                ], 'Payment confirmed successfully');
            }
            
            return $this->errorResponse('Payment has not been completed', 400);
            
        } catch (\Exception $e) {
            Log::error('Payment confirmation failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to confirm payment: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Process a refund for a payment
     */
    public function refund(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'reason' => 'sometimes|string|max:255'
        ]);
        
        try {
            $payment = $this->paymentService->findPayment($id);
            
            // Check if payment is refundable
            if (!in_array($payment->status, ['completed', 'processing'])) {
                return $this->errorResponse('This payment cannot be refunded', 400);
            }
            
            // Process refund with Stripe
            $amount = $validated['amount'] ?? $payment->amount;
            $reason = $validated['reason'] ?? 'requested_by_customer';
            
            $refundData = $this->stripeService->refundPayment(
                $payment->stripe_payment_intent_id,
                $amount,
                $reason
            );
            
            if (!$refundData['success']) {
                return $this->errorResponse('Failed to process refund: ' . ($refundData['error'] ?? 'Unknown error'), 500);
            }
            
            // Update payment status
            $isFullRefund = $amount >= $payment->amount;
            $payment->update([
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refunded_amount' => $amount,
                'refund_id' => $refundData['refund_id'],
                'payment_details' => array_merge($payment->payment_details ?? [], [
                    'refund' => [
                        'amount' => $amount,
                        'reason' => $reason,
                        'refund_id' => $refundData['refund_id'],
                        'date' => now()->toIso8601String()
                    ]
                ])
            ]);
            
            // Update rental status if it's a full refund
            if ($isFullRefund) {
                $payment->rental->update([
                    'rental_status_id' => RentalStatus::where('slug', 'cancelled')->first()->id
                ]);
            }
            
            return $this->successResponse(
                new PaymentResource($payment->fresh()),
                'Refund processed successfully'
            );
            
        } catch (\Exception $e) {
            Log::error('Refund processing failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to process refund: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper method to create transaction records
     */
    private function createTransactionRecord(Rental $rental, ?int $userId, float $amount, string $type, string $description): void
    {
        $transaction = new \App\Models\Transaction();
        $transaction->uuid = (string) \Illuminate\Support\Str::uuid();
        $transaction->user_id = $userId;
        $transaction->type = $type;
        $transaction->status = 'completed';
        $transaction->amount = $amount;
        $transaction->fee = 0;
        $transaction->description = $description;
        $transaction->transaction_type = 'rental';
        $transaction->source_id = $rental->id;
        $transaction->source_type = 'App\\Models\\Rental';
        $transaction->save();
    }
} 