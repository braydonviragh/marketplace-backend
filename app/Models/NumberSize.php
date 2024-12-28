<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NumberSize extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_detailed_sizes')
            ->where('size_type', 'number');
    }
} 