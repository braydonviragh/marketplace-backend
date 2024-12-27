<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShoeSize extends Model
{
    protected $fillable = [
        'size',
        'display_name',
        'description',
        'order',
        'is_active'
    ];

    protected $casts = [
        'size' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_detailed_sizes')
            ->where('size_type', 'shoe')
            ->withTimestamps();
    }
} 