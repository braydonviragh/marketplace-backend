<?php

namespace App\Repositories;

use App\Models\Rental;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\RentalStatus;

class RentalRepository extends BaseRepository
{
    public function __construct(Rental $model)
    {
        parent::__construct($model);
    }

    public function create(array $data): Rental
    {
        return $this->model->create($data);
    }

    public function getFilteredRentals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'product', 'rentalStatus']);

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by status
        if (isset($filters['status_id'])) {
            $query->whereHas('rentalStatus', function($q) use ($filters) {
                $q->where('id', $filters['status_id']);
            });
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }

        // Filter by product
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Product-related filters
        $query->whereHas('product', function ($productQuery) use ($filters) {
            if (isset($filters['category_id'])) {
                $productQuery->where('category_id', $filters['category_id']);
            }

            if (isset($filters['brand_id'])) {
                $productQuery->where('brand_id', $filters['brand_id']);
            }

            if (isset($filters['color_id'])) {
                $productQuery->where('color_id', $filters['color_id']);
            }

            // Size filters
            if (isset($filters['letter_size_id'])) {
                $productQuery->where('letter_size_id', $filters['letter_size_id']);
            }

            if (isset($filters['number_size_id'])) {
                $productQuery->where('number_size_id', $filters['number_size_id']);
            }

            if (isset($filters['waist_size_id'])) {
                $productQuery->where('waist_size_id', $filters['waist_size_id']);
            }

            // Price range filter
            if (isset($filters['price_min'])) {
                $productQuery->where('price', '>=', $filters['price_min']);
            }

            if (isset($filters['price_max'])) {
                $productQuery->where('price', '<=', $filters['price_max']);
            }
        });

        // Search by related product name or user name
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->whereHas('product', function ($subQ) use ($filters) {
                    $subQ->where('title', 'like', "%{$filters['search']}%");
                })->orWhereHas('user', function ($subQ) use ($filters) {
                    $subQ->where('name', 'like', "%{$filters['search']}%");
                });
            });
        }

        // Apply sorting
        if (isset($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
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
        $rentalStatus = RentalStatus::where('slug', $status)->first();
        
        if (!$rentalStatus) {
            throw new \Exception('Invalid status');
        }

        return $rental->update(['rental_status_id' => $rentalStatus->id]);
    }
}