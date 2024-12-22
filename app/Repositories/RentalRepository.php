<?php

namespace App\Repositories;

use App\Models\Rental;
use Illuminate\Database\Eloquent\Builder;

class RentalRepository extends BaseRepository
{
    public function __construct(Rental $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->query()
            ->with(['renter', 'owner', 'listing'])
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($filters['date_range'] ?? null, function (Builder $query, array $dateRange) {
                $query->whereBetween('start_date', $dateRange);
            })
            ->when($filters['category_id'] ?? null, function (Builder $query, int $categoryId) {
                $query->whereHas('listing', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($filters['min_price'] ?? null, function (Builder $query, float $price) {
                $query->where('total_price', '>=', $price);
            })
            ->when($filters['max_price'] ?? null, function (Builder $query, float $price) {
                $query->where('total_price', '<=', $price);
            })
            ->when($filters['user_id'] ?? null, function (Builder $query, int $userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('renter_id', $userId)
                      ->orWhere('owner_id', $userId);
                });
            })
            ->latest()
            ->paginate($perPage);
    }
} 