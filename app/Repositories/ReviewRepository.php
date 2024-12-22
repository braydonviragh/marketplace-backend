<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;

class ReviewRepository extends BaseRepository
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->query()
            ->with(['reviewer', 'reviewee', 'rental'])
            ->when($filters['user_id'] ?? null, function (Builder $query, int $userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('reviewer_id', $userId)
                      ->orWhere('reviewee_id', $userId);
                });
            })
            ->when($filters['rating'] ?? null, function (Builder $query, int $rating) {
                $query->where('rating', $rating);
            })
            ->when(isset($filters['is_approved']), function (Builder $query) use ($filters) {
                $query->where('is_approved', $filters['is_approved']);
            })
            ->when($filters['sort'] ?? null, function (Builder $query, string $sort) {
                [$column, $direction] = explode(',', $sort);
                $query->orderBy($column, $direction ?? 'desc');
            }, function (Builder $query) {
                $query->latest();
            })
            ->paginate($perPage);
    }
} 