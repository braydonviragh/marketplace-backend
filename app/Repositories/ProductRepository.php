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
        $query = $this->model->with(['user', 'category', 'media', 'brand', 'color']);

        // Basic filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['color_id'])) {
            $query->where('color_id', $filters['color_id']);
        }

        // Size filters
        if (isset($filters['letter_size_id'])) {
            $query->where('letter_size_id', $filters['letter_size_id']);
        }

        if (isset($filters['number_size_id'])) {
            $query->where('number_size_id', $filters['number_size_id']);
        }

        if (isset($filters['waist_size_id'])) {
            $query->where('waist_size_id', $filters['waist_size_id']);
        }

        // Price range
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

        // Availability filter
        if (isset($filters['is_available'])) {
            $query->where('is_available', $filters['is_available']);
        }

        // Search functionality
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
                  ->orWhereHas('brand', function ($subQ) use ($filters) {
                      $subQ->where('name', 'like', "%{$filters['search']}%");
                  })
                  ->orWhereHas('category', function ($subQ) use ($filters) {
                      $subQ->where('name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        // Date range filters
        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        // Sorting
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
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
            }
        } else {
            $query->latest();
        }

        // Availability check
        $query->where('is_available', true);

        return $query->paginate($perPage);
    }

    public function findProductWithRelations(int $id): Product
    {
        return $this->model->with([
            'user',
            'category',
            'media',
            'color',
            'letterSize',
            'waistSize',
            'numberSize'
        ])->findOrFail($id);
    }

    public function createProduct(array $data): Product
    {
        return $this->model->create($data);
    }
} 