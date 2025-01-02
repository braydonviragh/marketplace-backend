<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShoeSize extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'size',
        'display_name',
        'description',
    ];

    protected $casts = [
        'size' => 'decimal:1',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_detailed_sizes')
            ->where('user_shoe_size_id', $this->id);
    }
} 