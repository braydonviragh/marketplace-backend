<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'provider',
        'provider_id',
        'provider_token',
        'timezone',
        'locale',
        'country_code',
        'region_code',
        'account_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'provider_token' => 'encrypted:array',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
    ];

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function listings()
    {
        return $this->hasMany(ClothesListing::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    // Security Methods
    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts');
        
        if ($this->login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
            $this->save();
        }
    }

    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    // OAuth Methods
    public static function findOrCreateFromOAuth($oauthUser, string $provider): self
    {
        return self::firstOrCreate(
            [
                'email' => $oauthUser->getEmail(),
                'provider' => $provider,
            ],
            [
                'first_name' => $oauthUser->getName(),
                'last_name' => $oauthUser->getName(),
                'provider_id' => $oauthUser->getId(),
                'email_verified_at' => now(),
            ]
        );
    }

    // Role & Permission Methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    // Region Methods
    public function updateRegion(string $countryCode, string $regionCode): void
    {
        $this->update([
            'country_code' => strtoupper($countryCode),
            'region_code' => strtoupper($regionCode),
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInRegion($query, string $countryCode, string $regionCode)
    {
        return $query->where('country_code', strtoupper($countryCode))
                    ->where('region_code', strtoupper($regionCode));
    }
} 