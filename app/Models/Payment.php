<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'rental_id', 
        'payer_id', 
        'payee_id',
        'amount', 
        'platform_fee',
        'owner_amount',
        'currency',
        'payment_method',
        'status',
        'stripe_payment_intent_id',
        'refund_id',
        'refunded_amount',
        'payment_details'
    ];
    
    protected $casts = [
        'amount' => 'float',
        'platform_fee' => 'float',
        'owner_amount' => 'float',
        'refunded_amount' => 'float',
        'payment_details' => 'array'
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
    
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
    
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
} 