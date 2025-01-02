<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preference extends Model
{
    protected $fillable = [
        'user_id', 'language'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 