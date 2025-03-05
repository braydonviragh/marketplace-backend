<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\StripeAccount;

class StripeRepository
{
    public function getAccount(User $user): ?StripeAccount
    {
        return $user->stripeAccount;
    }

    public function createAccount(User $user, array $data): StripeAccount
    {
        return $user->stripeAccount()->create([
            'account_id' => $data['account_id'],
            'account_enabled' => $data['account_enabled'],
        ]);
    }

    public function updateAccount(StripeAccount $account, array $data): bool
    {
        return $account->update($data);
    }
} 