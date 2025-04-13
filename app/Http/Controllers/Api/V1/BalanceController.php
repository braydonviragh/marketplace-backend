<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTransactionRequest;
use App\Http\Resources\UserTransactionResource;
use App\Models\UserTransaction;
use App\Models\UserBalance;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\StripeAccount;
use App\Http\Resources\UserBalanceResource;
use App\Http\Requests\Balance\WithdrawRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BalanceController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->middleware('auth:sanctum');
        $this->stripeService = $stripeService;
    }

    /**
     * Get the authenticated user's balance
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance()
    {
        // Get or create user balance
        $balance = UserBalance::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0
            ]
        );

        return response()->json([
            'success' => true,
            'data' => new UserBalanceResource($balance)
        ]);
    }
    
    /**
     * Prepare for withdrawal
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiateWithdraw()
    {
        // Get user balance
        $balance = UserBalance::where('user_id', Auth::id())->first();
        
        if (!$balance || $balance->available_balance <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance for withdrawal'
            ], 400);
        }
        
        // Check if user has a Stripe account
        $stripeAccount = StripeAccount::where('user_id', Auth::id())->first();
        
        if (!$stripeAccount || !$stripeAccount->account_id) {
            return response()->json([
                'success' => false,
                'message' => 'You need to connect a Stripe account to withdraw funds',
                'should_connect_stripe' => true
            ], 400);
        }
        
        if (!$stripeAccount->account_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Your Stripe account needs to be verified before withdrawing funds',
                'should_verify_stripe' => true
            ], 400);
        }
        
        // Return available balance info
        return response()->json([
            'success' => true,
            'data' => [
                'available_balance' => $balance->available_balance,
                'minimum_withdrawal' => config('app.minimum_withdrawal', 1.00),
                'account_id' => $stripeAccount->account_id
            ]
        ]);
    }
    
    /**
     * Process a withdrawal request
     * 
     * @param WithdrawRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmWithdraw(WithdrawRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Validate balance
            $balance = UserBalance::where('user_id', Auth::id())->firstOrFail();
            
            $amount = $request->input('amount');
            
            // Check minimum withdrawal amount
            $minimumWithdrawal = config('app.minimum_withdrawal', 1.00);
            if ($amount < $minimumWithdrawal) {
                return response()->json([
                    'success' => false,
                    'message' => "Minimum withdrawal amount is $$minimumWithdrawal"
                ], 400);
            }
            
            // Check available balance
            if ($balance->available_balance < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance for withdrawal'
                ], 400);
            }
            
            // Get Stripe account
            $stripeAccount = StripeAccount::where('user_id', Auth::id())->firstOrFail();
            
            if (!$stripeAccount->account_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your Stripe account needs to be verified before withdrawing funds'
                ], 400);
            }
            
            // Calculate fee (if any)
            $feePercentage = config('app.withdrawal_fee_percentage', 0);
            $fee = ($amount * $feePercentage) / 100;
            $amountAfterFee = $amount - $fee;
            
            // Create withdrawal record
            $withdrawal = Withdrawal::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => Auth::id(),
                'amount' => $amount,
                'fee' => $fee,
                'status' => 'pending',
                'destination_type' => 'stripe_account',
                'destination_details' => json_encode([
                    'account_id' => $stripeAccount->account_id
                ]),
                'notes' => $request->input('notes')
            ]);
            
            // Create transaction record
            $transaction = Transaction::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => Auth::id(),
                'type' => 'debit',
                'status' => 'pending',
                'amount' => $amount,
                'fee' => $fee,
                'description' => 'Withdrawal to connected Stripe account',
                'transaction_type' => 'withdrawal',
                'source_id' => $withdrawal->id,
                'source_type' => 'App\\Models\\Withdrawal',
                'metadata' => json_encode([
                    'withdrawal_id' => $withdrawal->id,
                    'amount_after_fee' => $amountAfterFee
                ])
            ]);
            
            // Update user balance
            $balance->available_balance -= $amount;
            $balance->total_withdrawn += $amount;
            $balance->save();
            
            // Process transfer with Stripe
            if (app()->environment('production')) {
                // Process transfer in production
                $transferResult = $this->stripeService->createPayout(Auth::user(), $amountAfterFee);
                
                if (!$transferResult['success']) {
                    throw new \Exception($transferResult['error'] ?? 'Failed to process transfer');
                }
                
                // Update withdrawal with Stripe details
                $withdrawal->update([
                    'status' => 'processing',
                    'stripe_transfer_id' => $transferResult['transaction_id'] ?? null,
                    'processed_at' => now()
                ]);
                
                // Update transaction status
                $transaction->update([
                    'status' => 'processing',
                    'stripe_transaction_id' => $transferResult['transaction_id'] ?? null
                ]);
            } else {
                // In development, mark as completed for testing
                $withdrawal->update([
                    'status' => 'completed',
                    'stripe_transfer_id' => 'test_transfer_' . uniqid(),
                    'processed_at' => now()
                ]);
                
                $transaction->update([
                    'status' => 'completed',
                    'stripe_transaction_id' => 'test_txn_' . uniqid()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request has been processed successfully',
                'data' => [
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $amount,
                    'fee' => $fee,
                    'amount_after_fee' => $amountAfterFee,
                    'status' => $withdrawal->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process a withdrawal request (alias for confirmWithdraw)
     * 
     * @param WithdrawRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(WithdrawRequest $request)
    {
        return $this->confirmWithdraw($request);
    }
    
    /**
     * Get withdrawal history for the authenticated user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawalHistory(Request $request)
    {
        $withdrawals = Withdrawal::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    /**
     * Admin method to get any user's balance
     */
    public function getUserBalance(int $userId): JsonResponse
    {
        $balance = UserBalance::where('user_id', $userId)
            ->firstOrCreate([
                'user_id' => $userId,
                'balance' => 0
            ]);

        return response()->json([
            'data' => [
                'user_id' => $userId,
                'balance' => $balance->balance
            ]
        ]);
    }

    /**
     * Get authenticated user's transactions
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $transactions = UserTransaction::where('user_id', $userId)
            ->with([
                'rental.offer',
                'rental.offer.product',
                'rental.offer.product.media',
                'rental.rentalStatus'
            ])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => UserTransactionResource::collection($transactions),
            'meta' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    /**
     * Admin method to get any user's transactions
     */
    public function getUserTransactions(Request $request, int $userId): JsonResponse
    {
        $transactions = UserTransaction::where('user_id', $userId)
            ->with([
                'rental.offer.product.media',
                'rental.rentalStatus'
            ])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => UserTransactionResource::collection($transactions),
            'meta' => [
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }
} 