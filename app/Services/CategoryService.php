<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function getAllCategories(): Collection
    {
        return Category::active()->orderBy('name')->get();
    }

    public function getPaginatedCategories(int $perPage = 15): LengthAwarePaginator
    {
        return Category::orderBy('name')->paginate($perPage);
    }

    public function findCategory(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function createCategory(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $category->update($data);
        return $category->fresh();
    }

    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }

    public function toggleStatus(Category $category): Category
    {
        $category->update(['is_active' => !$category->is_active]);
        return $category->fresh();
    }
} 