<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Services\SocialAuthService;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Exceptions\SocialAuthException;

class SocialAuthController extends Controller
{
    protected SocialAuthService $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    public function redirect(string $provider)
    {
        try {
            return Socialite::driver($provider)->stateless()->redirect();
        } catch (\Exception $e) {
            throw new SocialAuthException("Unable to redirect to {$provider}");
        }
    }

    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);
            $token = $user->createToken('social_auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Successfully authenticated with ' . ucfirst($provider));
            
        } catch (\Exception $e) {
            throw new SocialAuthException('Social authentication failed');
        }
    }
} 