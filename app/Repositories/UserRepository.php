<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function createWithProfile(array $userData, array $profileData): User
    {
        $user = $this->create($userData);
        $user->profile()->create($profileData);
        return $user->load('profile');
    }

    public function updateWithProfile(User $user, array $userData, array $profileData = []): User
    {
        $this->update($user, $userData);
        
        if ($profileData) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        }

        return $user->load('profile');
    }

    public function getFilteredUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Handle search filters
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Handle relations
        if (isset($filters['with'])) {
            $relations = explode(',', $filters['with']);
            
            // Only load allowed relations
            $allowedRelations = ['profile'];
            $validRelations = array_intersect($relations, $allowedRelations);
            
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }

        return $query->latest()->paginate($perPage);
    }
} 