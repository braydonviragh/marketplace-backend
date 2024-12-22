<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();
        
        return $this->resourceResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    public function show(Category $category): JsonResponse
    {
        return $this->resourceResponse(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());
        
        return $this->resourceResponse(
            new CategoryResource($category),
            'Category created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->updateCategory($category, $request->validated());
        
        return $this->resourceResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->deleteCategory($category);
        
        return $this->messageResponse(
            'Category deleted successfully',
            Response::HTTP_OK
        );
    }

    public function toggleStatus(Category $category): JsonResponse
    {
        $category = $this->categoryService->toggleStatus($category);
        
        return $this->resourceResponse(
            new CategoryResource($category),
            'Category status updated successfully'
        );
    }
} 