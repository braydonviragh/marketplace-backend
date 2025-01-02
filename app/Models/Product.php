<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'category_id',
        'style_id',
        'title',
        'description',
        'brand',
        'price',
        'condition',
        'size_id',
        'specifications',
        'is_available',
        'city',
        'province',
        'postal_code',
        'location'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'specifications' => 'array',
        'views_count' => 'integer',
    ];

    protected $attributes = [
        'is_available' => true,
        'views_count' => 0,
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    // Size relationships based on size_type
    public function letterSize(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    public function numberSize(): BelongsTo
    {
        return $this->belongsTo(NumberSize::class, 'size_id')
            ->where('size_type', 'number');
    }

    public function waistSize(): BelongsTo
    {
        return $this->belongsTo(WaistSize::class, 'size_id')
            ->where('size_type', 'waist');
    }

    public function shoeSize(): BelongsTo
    {
        return $this->belongsTo(ShoeSize::class, 'size_id')
            ->where('size_type', 'shoe');
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

    public function scopeBySize($query, $sizeType, $sizeId)
    {
        return $query->where('size_type', $sizeType)
                    ->where('size_id', $sizeId);
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
    protected function getSizeAttribute($value)
    {
        if ($this->size_type === 'one_size') {
            return 'One Size';
        }
        return $value;
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
} 