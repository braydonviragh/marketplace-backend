<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'abbreviation',
    ];

    /**
     * Get the provinces for the country.
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    /**
     * Get the user profiles for the country.
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
} 