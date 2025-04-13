<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\UserBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            // Verify the event came from Stripe
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                return $this->handlePaymentIntentSucceeded($event->data->object);
                
            case 'payment_intent.payment_failed':
                return $this->handlePaymentIntentFailed($event->data->object);
                
            case 'charge.refunded':
                return $this->handleChargeRefunded($event->data->object);
                
            case 'transfer.paid':
                return $this->handleTransferPaid($event->data->object);
                
            case 'account.updated':
                return $this->handleAccountUpdated($event->data->object);
                
            default:
                Log::info('Unhandled Stripe event', [
                    'type' => $event->type,
                    'id' => $event->id
                ]);
                return response()->json(['status' => 'success', 'message' => 'Received unhandled event']);
        }
    }
    
    /**
     * Handle payment_intent.succeeded event
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment Intent Succeeded', [
            'id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100
        ]);
        
        try {
            // Find the payment record
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
            
            if (!$payment) {
                Log::warning('Payment record not found for payment intent', [
                    'payment_intent_id' => $paymentIntent->id
                ]);
                return response()->json(['status' => 'success', 'message' => 'Webhook processed']);
            }
            
            // If payment is already marked as completed, skip processing
            if ($payment->status === 'completed') {
                return response()->json(['status' => 'success', 'message' => 'Payment already processed']);
            }
            
            // Mark payment as completed
            $payment->status = 'completed';
            $payment->save();
            
            // Create transaction record for the owner
            $this->createTransactionRecord(
                $payment->rental,
                $payment->payee_id,
                $payment->owner_amount,
                'credit',
                'Rental payment received (80%)'
            );
            
            // Create transaction record for platform fee
            $this->createTransactionRecord(
                $payment->rental,
                null, // Platform account
                $payment->platform_fee,
                'credit',
                'Platform fee from rental (20%)'
            );
            
            // Update user balance
            $this->updateUserBalance($payment->payee_id, $payment->owner_amount);
            
            return response()->json(['status' => 'success', 'message' => 'Payment intent handled successfully']);
        } catch (\Exception $e) {
            Log::error('Error handling payment_intent.succeeded', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntent->id
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle payment_intent.payment_failed event
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        Log::info('Payment Intent Failed', [
            'id' => $paymentIntent->id,
            'error' => $paymentIntent->last_payment_error ?? 'Unknown error'
        ]);
        
        try {
            // Find the payment record
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
            
            if (!$payment) {
                Log::warning('Payment record not found for failed payment intent', [
                    'payment_intent_id' => $paymentIntent->id
                ]);
                return response()->json(['status' => 'success', 'message' => 'Webhook processed']);
            }
            
            // Update payment status
            $payment->status = 'failed';
            $payment->payment_details = array_merge($payment->payment_details ?? [], [
                'failure_message' => $paymentIntent->last_payment_error->message ?? 'Payment failed',
                'failure_code' => $paymentIntent->last_payment_error->code ?? null,
                'failed_at' => now()->toIso8601String()
            ]);
            $payment->save();
            
            return response()->json(['status' => 'success', 'message' => 'Failed payment handled']);
        } catch (\Exception $e) {
            Log::error('Error handling payment_intent.payment_failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntent->id
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle charge.refunded event
     */
    private function handleChargeRefunded($charge)
    {
        Log::info('Charge Refunded', [
            'id' => $charge->id,
            'payment_intent' => $charge->payment_intent,
            'amount_refunded' => $charge->amount_refunded / 100
        ]);
        
        try {
            // Find the payment record by payment intent ID
            $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();
            
            if (!$payment) {
                Log::warning('Payment record not found for refunded charge', [
                    'charge_id' => $charge->id,
                    'payment_intent_id' => $charge->payment_intent
                ]);
                return response()->json(['status' => 'success', 'message' => 'Webhook processed']);
            }
            
            // Is this a full or partial refund?
            $isFullRefund = $charge->amount_refunded >= $charge->amount;
            
            // Update payment status and refund details
            $payment->status = $isFullRefund ? 'refunded' : 'partially_refunded';
            $payment->refunded_amount = $charge->amount_refunded / 100;
            $payment->payment_details = array_merge($payment->payment_details ?? [], [
                'refund' => [
                    'charge_id' => $charge->id,
                    'amount_refunded' => $charge->amount_refunded / 100,
                    'is_full_refund' => $isFullRefund,
                    'refunded_at' => now()->toIso8601String()
                ]
            ]);
            $payment->save();
            
            // Update user balance - remove the credited amount
            if ($payment->payee_id) {
                $refundAmount = ($payment->owner_amount / $payment->amount) * ($charge->amount_refunded / 100);
                $this->updateUserBalance($payment->payee_id, -$refundAmount);
                
                // Create refund transaction record
                Transaction::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $payment->payee_id,
                    'type' => 'debit', 
                    'status' => 'completed',
                    'amount' => $refundAmount,
                    'fee' => 0,
                    'description' => 'Refund processed for rental',
                    'transaction_type' => 'refund',
                    'source_id' => $payment->id,
                    'source_type' => 'App\\Models\\Payment',
                ]);
            }
            
            return response()->json(['status' => 'success', 'message' => 'Refund handled successfully']);
        } catch (\Exception $e) {
            Log::error('Error handling charge.refunded', [
                'error' => $e->getMessage(),
                'charge_id' => $charge->id
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle transfer.paid event
     */
    private function handleTransferPaid($transfer)
    {
        Log::info('Transfer Paid', [
            'id' => $transfer->id,
            'amount' => $transfer->amount / 100,
            'destination' => $transfer->destination
        ]);
        
        // This event confirms money was sent to a connected account
        // Usually we don't need to handle this event except for logging purposes
        
        return response()->json(['status' => 'success', 'message' => 'Transfer logged']);
    }
    
    /**
     * Handle account.updated event
     */
    private function handleAccountUpdated($account)
    {
        Log::info('Stripe Account Updated', [
            'id' => $account->id,
            'charges_enabled' => $account->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled
        ]);
        
        try {
            // Find the StripeAccount record by account ID
            $stripeAccount = \App\Models\StripeAccount::where('account_id', $account->id)->first();
            
            if (!$stripeAccount) {
                Log::warning('Stripe account record not found', [
                    'account_id' => $account->id
                ]);
                return response()->json(['status' => 'success', 'message' => 'Webhook processed']);
            }
            
            // Update account details
            $stripeAccount->account_enabled = $account->charges_enabled && $account->payouts_enabled;
            
            // If the account is now fully verified and wasn't before, update the timestamp
            if ($stripeAccount->account_enabled && !$stripeAccount->account_verified_at) {
                $stripeAccount->account_verified_at = now();
            }
            
            $stripeAccount->save();
            
            return response()->json(['status' => 'success', 'message' => 'Account update handled successfully']);
        } catch (\Exception $e) {
            Log::error('Error handling account.updated', [
                'error' => $e->getMessage(),
                'account_id' => $account->id
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Create transaction record
     */
    private function createTransactionRecord($rental, $userId, $amount, $type, $description)
    {
        return Transaction::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $userId,
            'type' => $type,
            'status' => 'completed',
            'amount' => $amount,
            'fee' => 0,
            'description' => $description,
            'transaction_type' => 'rental',
            'source_id' => $rental->id,
            'source_type' => 'App\\Models\\Rental',
        ]);
    }
    
    /**
     * Update user's balance
     */
    private function updateUserBalance($userId, $amount)
    {
        if (!$userId) return;
        
        $userBalance = UserBalance::firstOrCreate(
            ['user_id' => $userId],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0
            ]
        );
        
        if ($amount > 0) {
            // Credit - add to pending balance first
            $userBalance->pending_balance += $amount;
            $userBalance->total_earned += $amount;
        } else {
            // Debit/refund - deduct from available balance first, then pending if needed
            $absAmount = abs($amount);
            
            if ($userBalance->available_balance >= $absAmount) {
                $userBalance->available_balance -= $absAmount;
            } else {
                $remainingAmount = $absAmount - $userBalance->available_balance;
                $userBalance->available_balance = 0;
                $userBalance->pending_balance = max(0, $userBalance->pending_balance - $remainingAmount);
                $userBalance->total_earned = max(0, $userBalance->total_earned - $absAmount);
            }
        }
        
        $userBalance->save();
        
        return $userBalance;
    }
} 