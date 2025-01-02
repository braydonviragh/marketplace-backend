<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Traits\WithDynamicRelations;
use App\Http\Resources\SimpleUserResource;

class UserController extends Controller
{
    protected UserService $userService;
    use WithDynamicRelations;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(UserRequest $request): JsonResponse
    {
        $users = $this->userService->getUsers(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new UserCollection($users),
            'Users retrieved successfully'
        );
    }

    public function getSimpleProfile(User $user)
    {
        return new SimpleUserResource($user);
    }


    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findUser($id);
        
        // Load all relevant relations for a complete user profile
        $user->load([
            'profile',
            'profile.style',
            'detailedSizes.letterSize',
            'detailedSizes.waistSize',
            'detailedSizes.numberSize',
            'brands'
        ]);
        
        return $this->resourceResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return $this->resourceResponse(
            new UserResource($user),
            'User created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        
        $user = $this->userService->updateUser($user, $request->validated());

        return $this->resourceResponse(
            new UserResource($user),
            'User updated successfully'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        
        $this->userService->deleteUser($user);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}