<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\UserProfile;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserProfileResource;
use App\Http\Requests\Auth\CompleteOnboardingRequest;

class OnboardingController extends Controller
{
    use ApiResponse;

    public function complete(CompleteOnboardingRequest $request)
    {
        $user = User::findOrFail($request->user_id);
        
        // Check if profile already exists
        if ($user->profile) {
            return $this->errorResponse('Profile already exists for this user', 422);
        }

        // Handle profile picture upload
        $profilePicturePath = $request->file('profile_picture')->store('profiles', 'public');

        // Create user profile
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'profile_picture' => $profilePicturePath,
            'username' => $request->username,
            'name' => $request->name,
            'birthday' => $request->birthday,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'country' => 'Canada',
            'style_id' => $request->style_id,
            'language' => 'en',
            'preferences' => [
                'preferred_brands' => $request->preferred_brands,
                'sizes' => $request->sizes,
            ]
        ]);

        // Sync brand preferences
        $user->brands()->sync($request->preferred_brands);

        // Sync size preferences
        $user->sizes()->sync($request->sizes['general']);
        if (isset($request->sizes['number'])) {
            $user->numberSizes()->sync($request->sizes['number']);
        }
        if (isset($request->sizes['waist'])) {
            $user->waistSizes()->sync($request->sizes['waist']);
        }

        // Update user status
        $user->update(['onboarding_completed' => true]);

        return $this->resourceResponse(
            new UserProfileResource($profile->fresh()),
            'Onboarding completed successfully'
        );
    }
} 