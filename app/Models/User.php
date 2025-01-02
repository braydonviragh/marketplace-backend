<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'name',
        'email',
        'phone_number',
        'password',
        'role',
        'terms_accepted',
        'terms_accepted_at',
        'is_active',
        'email_verified_at',
        'phone_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'terms_accepted' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'user_brand_preferences')
            ->withTimestamps();
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'user_sizes')
            ->withTimestamps();
    }

    public function numberSizes(): BelongsToMany
    {
        return $this->belongsToMany(NumberSize::class, 'user_detailed_sizes')
            ->where('size_type', 'number')
            ->withTimestamps();
    }

    public function waistSizes(): BelongsToMany
    {
        return $this->belongsToMany(WaistSize::class, 'user_detailed_sizes')
            ->where('size_type', 'waist')
            ->withTimestamps();
    }

    public function shoeSizes(): BelongsToMany
    {
        return $this->belongsToMany(ShoeSize::class, 'user_detailed_sizes')
            ->where('size_type', 'shoe')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_category_preferences')
            ->withTimestamps();
    }
} 