<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Size extends Model
{
    protected $fillable = ['size_name', 'description', 'category'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_sizes')
            ->withTimestamps();
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sizes')
            ->withTimestamps();
    }
} 