<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'rental_id',
        'amount',
        'type'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
} 