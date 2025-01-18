<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\UserBalance;

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->getFilteredUsers($filters, $perPage);
    }

    public function findUser(int $id): User
    {
        return $this->userRepository->findOrFail($id);
    }

    public function createUser(array $data): User
    {
        $userData = [
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'is_active' => true
        ];

        $profileData = [
            'username' => $data['username'] ?? null,
            'name' => $data['name'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? null,
            'style_id' => $data['style_id'] ?? null
        ];

        $user = $this->userRepository->createWithProfile($userData, $profileData);

        // Initialize user balance
        UserBalance::create([
            'user_id' => $user->id,
            'balance' => 0
        ]);

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $userData = array_filter([
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
        ]);

        $profileData = array_filter([
            'username' => $data['username'] ?? null,
            'name' => $data['name'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? null,
            'style_id' => $data['style_id'] ?? null,
        ]);

        return $this->userRepository->updateWithProfile($user, $userData, $profileData);
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }
} 