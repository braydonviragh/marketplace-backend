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

class BalanceController extends Controller
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    /**
     * Get authenticated user's balance
     */
    public function getBalance(): JsonResponse
    {
        $userId = 16; //auth()->id();

        $balance = UserBalance::where('user_id', $userId)
            ->firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );

        return response()->json([
            'data' => [
                'balance' => $balance->balance
            ]
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
     * Withdraw authenticated user's balance
     */
    public function withdraw(): JsonResponse
    {
        return DB::transaction(function () {
            $userId = 16; // TODO: Replace with auth()->id() in production
            $user = User::findOrFail($userId);
            
            $userBalance = UserBalance::where('user_id', $userId)
                ->firstOrCreate([
                    'user_id' => $userId,
                    'balance' => 0
                ]);

            if ($userBalance->balance <= 0) {
                return response()->json([
                    'message' => 'Insufficient balance'
                ], 400);
            }

            $amount = $userBalance->balance;

            // Process the payout through Stripe
            $payoutResult = $this->stripeService->createPayout($user, $amount);

            if (!$payoutResult['success']) {
                return response()->json([
                    'message' => 'Payout failed: ' . ($payoutResult['error'] ?? 'Unknown error')
                ], 500);
            }

            // Create withdrawal transaction
            UserTransaction::create([
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'remove',
                'stripe_transfer_id' => $payoutResult['transaction_id'] ?? null
            ]);

            // Reset balance to 0
            $userBalance->balance = 0;
            $userBalance->save();

            return response()->json([
                'message' => 'Withdrawal successful',
                'data' => [
                    'amount' => $amount,
                    'new_balance' => 0,
                    'transaction_id' => $payoutResult['transaction_id']
                ]
            ]);
        });
    }

    /**
     * Get authenticated user's transactions
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $userId = 16; //auth()->id();
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