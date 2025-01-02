<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'birthday',
        'postal_code',
        'city',
        'country',
        'style_preference',
        'language',
        'preferences'
    ];

    protected $casts = [
        'birthday' => 'string',
        'preferences' => 'array'
    ];

    protected $dates = ['birthday'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 