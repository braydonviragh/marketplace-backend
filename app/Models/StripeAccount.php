<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_id',
        'account_enabled',
        'account_verified_at',
        'account_details',
        'business_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'account_enabled' => 'boolean',
        'account_verified_at' => 'datetime',
        'account_details' => 'array',
    ];

    /**
     * Get the user that owns the Stripe account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if account is verified.
     * 
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->account_verified_at !== null;
    }

    /**
     * Set account as verified.
     * 
     * @return $this
     */
    public function markAsVerified()
    {
        if (!$this->isVerified()) {
            $this->account_verified_at = now();
            $this->save();
        }
        
        return $this;
    }
} 