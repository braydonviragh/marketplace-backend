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
        'email',
        'phone_number',
        'password',
        'role',
        'terms_accepted',
        'terms_accepted_at',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'onboarding_completed',
        'remember_token',
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
        'onboarding_completed' => 'boolean',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRentals(): HasMany
    {
        return $this->rentals()->whereHas('rentalStatus', function($q) {
            $q->where('slug', 'active');
        });
    }

    public function detailedSizes(): HasMany
    {
        return $this->hasMany(UserDetailedSize::class);
    }

    // Helper methods to get specific size types
    public function getLetterSizes()
    {
        return $this->detailedSizes()->whereNotNull('size_id')->with('letterSize');
    }

    public function getWaistSizes()
    {
        return $this->detailedSizes()->whereNotNull('waist_size_id')->with('waistSize');
    }

    public function getNumberSizes()
    {
        return $this->detailedSizes()->whereNotNull('number_size_id')->with('numberSize');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'user_brand_preferences');
    }

    public function balance(): HasOne
    {
        return $this->hasOne(UserBalance::class);
    }

    public function stripeAccount(): HasOne
    {
        return $this->hasOne(StripeAccount::class);
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'user_product_favorites', 'user_id', 'product_id');
    }

    /**
     * Check if the user has a specific role
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user can access another user's data
     *
     * @param int $userId
     * @return bool
     */
    public function canAccessUser(int $userId): bool
    {
        // Super admins can access any user
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        // Regular users can only access their own data
        return $this->id === (int) $userId;
    }
} 