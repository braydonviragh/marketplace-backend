<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserProfileResource;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController
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
} 