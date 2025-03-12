<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\UserProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function show(UserProfile $userProfile)
    {
        return new UserProfileResource($userProfile);
    }

    // Or for a collection
    public function index()
    {
        $profiles = UserProfile::paginate(20);
        return UserProfileResource::collection($profiles);
    }
    
    /**
     * Store or update the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Use Auth facade to get the authenticated user
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        // Validate the request data
        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:user_profiles,username,' . $user->id . ',user_id',
            'name' => 'sometimes|string|max:255',
            'birthday' => 'sometimes|nullable|date',
            'postal_code' => 'sometimes|nullable|string|max:20',
            'city' => 'sometimes|nullable|string|max:255',
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'province_id' => 'sometimes|nullable|exists:provinces,id',
            'style_id' => 'sometimes|nullable|exists:styles,id',
            'language' => 'sometimes|nullable|string|max:10',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
        ]);
        
        // Update or create the user profile
        $profile = UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );
        
        // Handle profile picture if provided
        if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
            // Remove existing profile picture if exists
            if ($profile->profilePicture) {
                $profile->profilePicture->delete();
            }
            
            // Store the new profile picture
            $file = $request->file('profile_picture');
            $path = $file->store('profile_pictures', 'public');
            
            $profile->media()->create([
                'disk' => 'public',
                'path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'is_primary' => true,
                'order' => 1
            ]);
        }
        
        // Load the profile with its relationships
        $profile->load(['style', 'country', 'province']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => new UserProfileResource($profile)
        ], Response::HTTP_OK);
    }
} 