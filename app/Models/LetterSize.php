<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LetterSize extends Model
{
    public $timestamps = false;
    
    protected $fillable = ['name', 'slug', 'description'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_detailed_sizes')
            ->where('letter_size_id', $this->id);
    }
} 