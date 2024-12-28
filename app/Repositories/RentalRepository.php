<?php

namespace App\Repositories;

use App\Models\Rental;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RentalRepository extends BaseRepository
{
    public function __construct(Rental $model)
    {
        parent::__construct($model);
    }

    public function getFilteredRentals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['product', 'user', 'payment', 'offer']);

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('rental_from', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('rental_to', '<=', $filters['date_to']);
        }

        // Filter by product
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Search by related product name or user name
        if (isset($filters['search'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%");
            })->orWhereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function getUserRentals(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['product', 'payment'])
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Rental
    {
        return $this->model->with(['product', 'user', 'payment'])->find($id);
    }

    public function updateStatus(Rental $rental, string $status): bool
    {
        return $rental->update(['status' => $status]);
    }
}