<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Product extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'style_id',
        'color_id',
        'title',
        'description',
        'brand',
        'price',
        'condition',
        'sizeable_type',
        'sizeable_id',
        'is_available',
        'city',
        'province',
        'postal_code',
        'location'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'views_count' => 'integer',
    ];

    protected $attributes = [
        'is_available' => true,
        'views_count' => 0,
    ];

    protected $with = ['sizeable', 'color'];

    protected $appends = ['size'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->orderBy('order');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function sizeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySize($query, $sizeableType, $sizeableId)
    {
        return $query->where('sizeable_type', $sizeableType)
                    ->where('sizeable_id', $sizeableId);
    }

    public function scopeByLocation($query, $latitude, $longitude, $radius = 50)
    {
        return $query->whereRaw(
            'ST_Distance_Sphere(location, POINT(?, ?)) <= ?',
            [$longitude, $latitude, $radius * 1000]
        );
    }

    public function scopeByStyle($query, $styleId)
    {
        return $query->where('style_id', $styleId);
    }

    // Accessors & Mutators
    protected function getSizeAttribute()
    {
        if (!$this->sizeable_type || !$this->sizeable) {
            return null;
        }

        return [
            'id' => $this->sizeable->id,
            'name' => $this->sizeable->name,
            'display_name' => $this->sizeable->display_name,
            'slug' => $this->sizeable->slug,
            'type' => $this->getSizeTypeFromClass()
        ];
    }

    private function getSizeTypeFromClass()
    {
        return match ($this->sizeable_type) {
            LetterSize::class => 'letter',
            NumberSize::class => 'number',
            WaistSize::class => 'waist',
            ShoeSize::class => 'shoe',
            default => null
        };
    }

    protected function getLocationAttribute($value)
    {
        if (!$value) {
            return null;
        }
        $coordinates = substr($value, 6, -1);
        [$longitude, $latitude] = explode(' ', $coordinates);
        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude
        ];
    }

    protected function setLocationAttribute($value)
    {
        if (is_array($value) && isset($value['latitude']) && isset($value['longitude'])) {
            $this->attributes['location'] = DB::raw("ST_GeomFromText('POINT({$value['longitude']} {$value['latitude']})')");
        }
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->primaryImage?->url;
    }
} 