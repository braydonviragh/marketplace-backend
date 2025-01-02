<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBrandPreference extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'brand_id'
    ];

    /**
     * Get the user that owns the brand preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the brand associated with this preference.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
} 