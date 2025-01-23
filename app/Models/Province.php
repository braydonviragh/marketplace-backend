<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'abbreviation',
    ];

    /**
     * Get the country that owns the province.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the user profiles for the province.
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
} 