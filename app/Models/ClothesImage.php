<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClothesImage extends Model
{
    protected $fillable = ['listing_id', 'image_path'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'listing_id');
    }
} 