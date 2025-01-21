<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UserProfile extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'username',
        'name',
        'profile_picture',
        'birthday',
        'postal_code',
        'city',
        'country',
        'style_id',
        'language',
    ];

    protected $casts = [
        'birthday' => 'string',
    ];

    // Define which relationships to eager load by default
    protected $with = [
        'style', 
        'user.detailedSizes.letterSize', 
        'user.detailedSizes.waistSize', 
        'user.detailedSizes.numberSize', 
        'user.brands'
    ];

    protected $appends = ['profile_picture_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class);
    }

    // Helper method to get all preferences in a structured format
    public function getAllPreferences(): array
    {
        return [
            'style' => $this->style,
            'sizes' => $this->user->detailedSizes->map(function ($size) {
                return [
                    'letter_size' => $size->letterSize,
                    'waist_size' => $size->waistSize,
                    'number_size' => $size->numberSize,
                ];
            }),
            'brands' => $this->user->brands,
        ];
    }

    /**
     * Get all media for the profile
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the profile picture
     */
    public function profilePicture(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')
            ->where('is_primary', true);
    }

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        return $this->profilePicture?->url;
    }
} 