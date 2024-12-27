<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getFilteredProducts($filters, $perPage);
    }

    public function findProduct(int $id): Product
    {
        return $this->productRepository->findOrFail($id);
    }

    public function createProduct(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $this->productRepository->update($product, $data);
        return $product->fresh();
    }

    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }
} 