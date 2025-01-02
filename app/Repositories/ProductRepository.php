<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getFilteredProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'category', 'images']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (isset($filters['size_type']) && isset($filters['size_id'])) {
            $query->where('size_type', $filters['size_type'])
                  ->where('size_id', $filters['size_id']);
        }

        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (isset($filters['province'])) {
            $query->where('province', $filters['province']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->where('is_available', true)
                    ->latest()
                    ->paginate($perPage);
    }

    public function findProductWithRelations(int $id): Product
    {
        return $this->model->with([
            'user',
            'category',
            'images',
            'color',
            'letterSize',
            'waistSize',
            'numberSize'
        ])->findOrFail($id);
    }
} 