<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rental_id',
        'payer_id',
        'payee_id',
        'payment_method',
        'payment_id',
        'amount',
        'currency',
        'status',
        'payment_details',
        'refund_details',
        'failure_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'json',
        'refund_details' => 'json'
    ];

    // Relationships
    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    // Payment Processing Methods
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'payment_details' => array_merge($this->payment_details ?? [], [
                'completed_at' => now()->toIso8601String(),
                'confirmation_number' => uniqid('PAY-', true)
            ])
        ]);
    }

    public function processRefund(float $amount, string $reason)
    {
        $this->update([
            'status' => $amount === $this->amount ? 'refunded' : 'partially_refunded',
            'refund_details' => [
                'amount' => $amount,
                'reason' => $reason,
                'refunded_at' => now()->toIso8601String(),
                'refund_id' => uniqid('REF-', true)
            ]
        ]);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
} 