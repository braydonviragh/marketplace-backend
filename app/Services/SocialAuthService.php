<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    public function findOrCreateUser(SocialiteUser $socialUser, string $provider): User
    {
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            $this->updateSocialInfo($user, $socialUser, $provider);
            return $user;
        }

        return $this->createUser($socialUser, $provider);
    }

    protected function updateSocialInfo(User $user, SocialiteUser $socialUser, string $provider): void
    {
        $user->update([
            "{$provider}_id" => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'social_provider' => $provider,
        ]);
    }

    protected function createUser(SocialiteUser $socialUser, string $provider): User
    {
        $names = $this->splitName($socialUser->getName());

        return User::create([
            'email' => $socialUser->getEmail(),
            'first_name' => $names['first_name'],
            'last_name' => $names['last_name'],
            'password' => bcrypt(Str::random(16)),
            'avatar' => $socialUser->getAvatar(),
            "{$provider}_id" => $socialUser->getId(),
            'social_provider' => $provider,
            'email_verified_at' => now(),
        ]);
    }

    protected function splitName(string $fullName): array
    {
        $parts = explode(' ', $fullName);
        return [
            'first_name' => array_shift($parts) ?? '',
            'last_name' => implode(' ', $parts) ?? '',
        ];
    }
} 