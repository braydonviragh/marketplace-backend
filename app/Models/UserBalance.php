<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBalance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 