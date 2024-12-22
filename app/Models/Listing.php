<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'brand',
        'size',
        'condition',
        'daily_price',
        'weekly_price',
        'monthly_price',
        'security_deposit',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'is_available',
        'availability_calendar',
        'unavailable_dates',
        'specifications',
        'care_instructions',
        'is_approved'
    ];

    protected $casts = [
        'daily_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_available' => 'boolean',
        'is_approved' => 'boolean',
        'featured' => 'boolean',
        'availability_calendar' => 'array',
        'unavailable_dates' => 'array',
        'specifications' => 'array',
        'care_instructions' => 'array',
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'listing_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                    ->where('is_approved', true);
    }

    public function scopeNearby($query, $latitude, $longitude, $radius = 25)
    {
        return $query->whereRaw(
            'ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?',
            [$longitude, $latitude, $radius * 1000]
        );
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function approve()
    {
        $this->update([
            'is_approved' => true,
            'approved_at' => now()
        ]);
    }
} 