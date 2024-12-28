<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Size extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['size_name', 'description', 'category'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_sizes');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_sizes');
    }
} 