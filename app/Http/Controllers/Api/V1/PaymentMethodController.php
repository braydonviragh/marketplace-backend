<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Stripe;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all payment methods for the authenticated user
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return PaymentMethodResource::collection($paymentMethods);
    }

    /**
     * Store a new payment method for the authenticated user
     *
     * @param StorePaymentMethodRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePaymentMethodRequest $request)
    {
        try {
            // Start transaction
            DB::beginTransaction();
            
            // Get the payment method details from Stripe
            $paymentMethodId = $request->input('payment_method_id');
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            
            // Attach the payment method to the customer
            if (!Auth::user()->stripe_customer_id) {
                throw new \Exception('User does not have a Stripe customer account');
            }
            
            $stripePaymentMethod->attach([
                'customer' => Auth::user()->stripe_customer_id,
            ]);
            
            // If this is the first payment method or is_default is true, make it default
            $makeDefault = $request->input('is_default', false);
            $existingPaymentMethods = PaymentMethod::where('user_id', Auth::id())->count();
            
            if ($makeDefault || $existingPaymentMethods === 0) {
                // If making this default, update any existing default payment methods
                if ($existingPaymentMethods > 0) {
                    PaymentMethod::where('user_id', Auth::id())
                        ->where('is_default', true)
                        ->update(['is_default' => false]);
                }
                
                $isDefault = true;
            } else {
                $isDefault = false;
            }
            
            // Create the payment method record in the database
            $paymentMethod = PaymentMethod::create([
                'user_id' => Auth::id(),
                'payment_method_id' => $paymentMethodId,
                'type' => $stripePaymentMethod->type,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'last4' => $stripePaymentMethod->card->last4 ?? null,
                'exp_month' => $stripePaymentMethod->card->exp_month ?? null,
                'exp_year' => $stripePaymentMethod->card->exp_year ?? null,
                'is_default' => $isDefault,
                'metadata' => json_encode([
                    'country' => $stripePaymentMethod->card->country ?? null,
                    'funding' => $stripePaymentMethod->card->funding ?? null,
                ]),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully',
                'data' => new PaymentMethodResource($paymentMethod),
            ]);
        } catch (ApiErrorException $e) {
            DB::rollBack();
            Log::error('Stripe error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment method: ' . $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding payment method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment method. Please try again.',
            ], 500);
        }
    }
    
    /**
     * Set a payment method as default
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefault($id)
    {
        try {
            DB::beginTransaction();
            
            // Find the payment method and verify ownership
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            // Update all payment methods to not be default
            PaymentMethod::where('user_id', Auth::id())
                ->where('is_default', true)
                ->update(['is_default' => false]);
            
            // Set this one as default
            $paymentMethod->is_default = true;
            $paymentMethod->save();
            
            // Update the default payment method with Stripe if needed
            if (Auth::user()->stripe_customer_id) {
                \Stripe\Customer::update(Auth::user()->stripe_customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethod->payment_method_id,
                    ],
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Default payment method updated',
                'data' => new PaymentMethodResource($paymentMethod),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting default payment method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update default payment method',
            ], 500);
        }
    }
    
    /**
     * Remove a payment method
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Find the payment method and verify ownership
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            
            // Store if it was the default
            $wasDefault = $paymentMethod->is_default;
            
            // Detach from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->payment_method_id);
            $stripePaymentMethod->detach();
            
            // Delete from database
            $paymentMethod->delete();
            
            // If it was the default, make another one the default
            if ($wasDefault) {
                $newDefault = PaymentMethod::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($newDefault) {
                    $newDefault->is_default = true;
                    $newDefault->save();
                    
                    // Update Stripe default payment method
                    if (Auth::user()->stripe_customer_id) {
                        \Stripe\Customer::update(Auth::user()->stripe_customer_id, [
                            'invoice_settings' => [
                                'default_payment_method' => $newDefault->payment_method_id,
                            ],
                        ]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Payment method removed successfully',
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe error removing payment method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payment method: ' . $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error removing payment method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payment method',
            ], 500);
        }
    }
} 