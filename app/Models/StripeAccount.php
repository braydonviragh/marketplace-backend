<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeAccount extends Model
{
    protected $table = 'stripe_accounts';

    protected $fillable = [
        'user_id',
        'customer_id',
        'account_id',
        'account_enabled',
        'account_details',
        'account_verified_at',
        'default_payment_method'
    ];

    protected $casts = [
        'account_enabled' => 'boolean',
        'account_details' => 'array',
        'account_verified_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 