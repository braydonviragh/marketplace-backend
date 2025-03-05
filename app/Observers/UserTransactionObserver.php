<?php

namespace App\Observers;

use App\Models\UserTransaction;
use App\Models\UserBalance;
use Illuminate\Support\Facades\DB;

class UserTransactionObserver
{
    /**
     * Update user balance after any transaction changes
     */
    private function updateUserBalance(UserTransaction $transaction): void
    {
        // Calculate total balance from all transactions
        $balance = UserTransaction::where('user_id', $transaction->user_id)
            ->selectRaw('SUM(CASE 
                WHEN type = "add" THEN amount 
                WHEN type = "remove" THEN -amount 
                ELSE 0 
            END) as total_balance')
            ->first()
            ->total_balance ?? 0;

        // Update or create user balance
        UserBalance::updateOrCreate(
            ['user_id' => $transaction->user_id],
            ['balance' => $balance]
        );
    }

    /**
     * Handle the UserTransaction "created" event.
     */
    public function created(UserTransaction $transaction): void
    {
        $this->updateUserBalance($transaction);
    }

    /**
     * Handle the UserTransaction "updated" event.
     */
    public function updated(UserTransaction $transaction): void
    {
        $this->updateUserBalance($transaction);
    }

    /**
     * Handle the UserTransaction "deleted" event.
     */
    public function deleted(UserTransaction $transaction): void
    {
        $this->updateUserBalance($transaction);
    }

    /**
     * Handle the UserTransaction "restored" event.
     */
    public function restored(UserTransaction $transaction): void
    {
        $this->updateUserBalance($transaction);
    }
} 