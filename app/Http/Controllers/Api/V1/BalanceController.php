<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserTransactionRequest;
use App\Http\Resources\UserTransactionResource;
use App\Models\UserTransaction;
use App\Models\UserBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function getBalance(): JsonResponse
    {
        $balance = UserBalance::where('user_id', auth()->id())
            ->firstOrCreate(['balance' => 0]);

        return response()->json([
            'data' => [
                'balance' => $balance->balance
            ]
        ]);
    }

    public function withdraw(): JsonResponse
    {
        return DB::transaction(function () {
            $userBalance = UserBalance::where('user_id', auth()->id())
                ->firstOrCreate(['balance' => 0]);

            if ($userBalance->balance <= 0) {
                return response()->json([
                    'message' => 'Insufficient balance'
                ], 400);
            }

            $amount = $userBalance->balance;

            // Create withdrawal transaction
            UserTransaction::create([
                'user_id' => auth()->id(),
                'amount' => $amount,
                'type' => 'remove'
            ]);

            // Reset balance to 0
            $userBalance->balance = 0;
            $userBalance->save();

            return response()->json([
                'message' => 'Withdrawal successful',
                'data' => [
                    'amount' => $amount,
                    'new_balance' => 0
                ]
            ]);
        });
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $transactions = UserTransaction::where('user_id', auth()->id())
            ->with(['rental.product'])
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