<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $users = $this->userService->getUsers(
            filters: $request->only(['search', 'region_code', 'role', 'sort']),
            perPage: $request->per_page ?? 15
        );

        return new UserCollection($users);
    }

    public function show(int $id)
    {
        $user = $this->userService->findUser($id);
        return new UserResource($user->load(['profile', 'listings']));
    }
}