<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WaistSize extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'size',
        'display_name',
        'description',
        'order',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_detailed_sizes')
            ->where('user_waist_size_id', $this->id);
    }
}