<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'amount',
        'status',
        'message',
        'valid_until',
        'counter_offer_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'valid_until' => 'datetime'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function counterOffer()
    {
        return $this->belongsTo(Offer::class, 'counter_offer_id');
    }
} 