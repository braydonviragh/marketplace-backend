<?php

namespace App\Models;

use App\Services\GeocodingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

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
        'country_id',
        'province_id',
        'style_id',
        'language',
        'latitude',
        'longitude',
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
        'user.brands',
        'country',
        'province'
    ];

    protected $appends = ['profile_picture_url'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($profile) {
            // If coordinates are being updated, update the spatial point
            if ($profile->isDirty(['latitude', 'longitude']) && 
                $profile->latitude && 
                $profile->longitude) {
                $profile->location = DB::raw("ST_GeomFromText('POINT({$profile->longitude} {$profile->latitude})')");
            }
            
            // If postal_code is being updated, update coordinates
            if ($profile->isDirty('postal_code') && $profile->postal_code) {
                $geocodingService = app(GeocodingService::class);
                $coordinates = $geocodingService->getCoordinatesFromPostalCode($profile->postal_code);
                
                if ($coordinates) {
                    $profile->latitude = $coordinates['latitude'];
                    $profile->longitude = $coordinates['longitude'];
                    $profile->location = DB::raw("ST_GeomFromText('POINT({$coordinates['longitude']} {$coordinates['latitude']})')");
                    
                    if (!$profile->city || $profile->isDirty('postal_code')) {
                        $profile->city = $coordinates['city'] ?? $profile->city;
                    }
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
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

    // Add a method to calculate distance to another profile or coordinates
    public function distanceTo($lat, $lng): ?float
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        // Calculate distance using MySQL's ST_Distance_Sphere
        return DB::select("
            SELECT ST_Distance_Sphere(
                point(?, ?),
                point(?, ?)
            ) * 0.001 as distance_km",
            [$this->longitude, $this->latitude, $lng, $lat]
        )[0]->distance_km;
    }

    public function getQueryablePreferences(): array
    {
        $preferences = [];
        
        // Add size preferences
        foreach ($this->user->detailedSizes as $size) {
            if ($size->letter_size_id) {
                $preferences['letter_size_ids'][] = $size->letter_size_id;
            }
            if ($size->waist_size_id) {
                $preferences['waist_size_ids'][] = $size->waist_size_id;
            }
            if ($size->number_size_id) {
                $preferences['number_size_ids'][] = $size->number_size_id;
            }
        }
        
        // Add brand preferences
        $preferences['brand_ids'] = $this->user->brands->pluck('id')->toArray();
        
        // Add location preference
        $preferences['city'] = $this->city;
        
        // Add style preference
        $preferences['style_id'] = $this->style_id;
        
        return $preferences;
    }
} 