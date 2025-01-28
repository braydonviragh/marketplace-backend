<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Expression;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getFilteredProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with([
            'user.profile',
            'category',
            'media',
            'brand',
            'color'
        ]);

        // If tailored filter is present, apply user preferences
        if (isset($filters['filter']) && $filters['filter'] === 'tailored') {
            
            //$user = auth()->user();

            //TODO remove this after testing
            $user = \App\Models\User::find(1);

            if ($user && $user->profile) {
                $preferences = $user->profile->getQueryablePreferences();
                
                // First apply mandatory city filter if it exists
                //TODO determine if tailored product should be filtered by city
                // if (!empty($preferences['city'])) {
                //     $query->whereHas('user.profile', function($subQ) use ($preferences) {
                //         $subQ->where('city', $preferences['city']);
                //     });
                // }
                
                // Then apply flexible size preferences
                $query->where(function($q) use ($preferences) {
                    // Match any size preference
                    if (!empty($preferences['letter_size_ids'])) {
                        $q->orWhereIn('letter_size_id', $preferences['letter_size_ids']);
                    }
                    if (!empty($preferences['waist_size_ids'])) {
                        $q->orWhereIn('waist_size_id', $preferences['waist_size_ids']);
                    }
                    if (!empty($preferences['number_size_ids'])) {
                        $q->orWhereIn('number_size_id', $preferences['number_size_ids']);
                    }
                    
                    // Match style
                    if (!empty($preferences['style_id'])) {
                        $q->where('style_id', $preferences['style_id']);
                    }
                });
            }
        }

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

        // Location-based filtering
        if (isset($filters['postal_code']) || (isset($filters['latitude']) && isset($filters['longitude']))) {
            $distance = $filters['distance'] ?? 50; // Default to 50km if not specified
            
            if (isset($filters['postal_code'])) {
                // Convert postal code to coordinates using geocoding service
                $geocodingService = app(GeocodingService::class);
                $coordinates = $geocodingService->getCoordinatesFromPostalCode($filters['postal_code']);
                
                if ($coordinates) {
                    $latitude = $coordinates['latitude'];
                    $longitude = $coordinates['longitude'];
                }
            } else {
                $latitude = $filters['latitude'];
                $longitude = $filters['longitude'];
            }

            if (isset($latitude) && isset($longitude)) {
                // Join with user_profiles to get seller's location
                $query->join('users', 'products.user_id', '=', 'users.id')
                    ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                    ->select('products.*')
                    ->selectRaw("
                        ST_Distance_Sphere(
                            point(user_profiles.longitude, user_profiles.latitude),
                            point(?, ?)
                        ) * 0.001 as distance_in_km",
                        [$longitude, $latitude]
                    )
                    ->whereNotNull('user_profiles.latitude')
                    ->whereNotNull('user_profiles.longitude')
                    ->having('distance_in_km', '<=', $distance);

                // Apply distance-based sorting if requested
                if (isset($filters['sort_by']) && $filters['sort_by'] === 'distance') {
                    $query->orderBy('distance_in_km', 'asc');
                }
            }
        }

        // Apply other sorting options
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
                // Distance sorting is handled in the location filtering section
            }
        } else {
            $query->latest();
        }

        // Ensure we're only getting available products
        $query->where('is_available', true);
        return $query->paginate($perPage);
    }

    /**
     * Get nearby products based on coordinates
     */
    public function getNearbyProducts(float $latitude, float $longitude, float $distance = 50, int $limit = 10): array
    {
        return $this->model
            ->join('users', 'products.user_id', '=', 'users.id')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('products.*')
            ->selectRaw("
                ST_Distance_Sphere(
                    point(user_profiles.longitude, user_profiles.latitude),
                    point(?, ?)
                ) * 0.001 as distance_in_km",
                [$longitude, $latitude]
            )
            ->whereNotNull('user_profiles.latitude')
            ->whereNotNull('user_profiles.longitude')
            ->where('products.is_available', true)
            ->having('distance_in_km', '<=', $distance)
            ->orderBy('distance_in_km', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get products grouped by city
     */
    public function getProductsByCity(int $limit = 10): array
    {
        return $this->model
            ->join('users', 'products.user_id', '=', 'users.id')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('user_profiles.city')
            ->selectRaw('COUNT(*) as product_count')
            ->where('products.is_available', true)
            ->whereNotNull('user_profiles.city')
            ->groupBy('user_profiles.city')
            ->orderBy('product_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
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