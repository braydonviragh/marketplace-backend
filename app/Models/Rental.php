<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rental extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'offer_id',
        'rental_status_id',
        'owner_id',
        'renter_id',
        'product_id',
        'start_date',
        'end_date',
        'total_price',
        'total_amount',
        'status',
        'is_balance_released',
        'balance_released_at'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'total_price' => 'float',
        'total_amount' => 'float',
        'is_balance_released' => 'boolean',
        'balance_released_at' => 'datetime',
    ];

    // Relationships
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function rentalStatus()
    {
        return $this->belongsTo(RentalStatus::class, 'rental_status_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // Delegate relationships through offer
    public function product()
    {
        return $this->offer->product();
    }

    public function user()
    {
        return $this->offer->user();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereHas('rentalStatus', function($q) {
            $q->where('slug', 'active');
        });
    }

    public function scopePending($query)
    {
        return $query->whereHas('rentalStatus', function($q) {
            $q->where('slug', 'pending'); 
        });
    }
}