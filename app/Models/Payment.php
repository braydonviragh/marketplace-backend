<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'rental_id', 'amount', 'status', 'payment_method'
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
} 