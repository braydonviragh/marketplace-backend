<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WaistSize extends Model
{
    protected $fillable = [
        'size',
        'display_name',
        'description',
        'order',
        'is_active'
    ];

    protected $casts = [
        'size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_detailed_sizes')
            ->where('size_type', 'waist')
            ->withTimestamps();
    }
} 