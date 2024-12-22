<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['region_code'] ?? null, function (Builder $query, string $region) {
                $query->where('region_code', $region);
            })
            ->when($filters['role'] ?? null, function (Builder $query, string $role) {
                $query->where('role', $role);
            })
            ->when($filters['sort'] ?? null, function (Builder $query, string $sort) {
                [$column, $direction] = explode(',', $sort);
                $query->orderBy($column, $direction ?? 'asc');
            }, function (Builder $query) {
                $query->latest();
            })
            ->paginate($perPage);
    }
} 