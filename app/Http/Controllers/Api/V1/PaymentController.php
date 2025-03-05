<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Http\Requests\PaymentRequest;
use App\Services\StripeService;
use App\Models\RentalStatus;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
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
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment has not been completed'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage()
            ], 500);
        }
    }
} 