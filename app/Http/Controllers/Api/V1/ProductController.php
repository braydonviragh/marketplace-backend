<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'color_id' => 'sometimes|exists:colors,id',
            'letter_size_id' => 'sometimes|exists:letter_sizes,id',
            'number_size_id' => 'sometimes|exists:number_sizes,id',
            'waist_size_id' => 'sometimes|exists:waist_sizes,id',
            'price_min' => 'sometimes|numeric|min:0',
            'price_max' => 'sometimes|numeric|gt:price_min',
            'city' => 'sometimes|string|max:100',
            'province' => 'sometimes|string|max:100',
            'is_available' => 'sometimes|boolean',
            'search' => 'sometimes|string|max:255',
            'created_from' => 'sometimes|date_format:Y-m-d H:i:s',
            'created_to' => 'sometimes|date_format:Y-m-d H:i:s|after:created_from',
            'sort_by' => 'sometimes|in:price_asc,price_desc,date_asc,date_desc,title_asc,title_desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $products = $this->productService->getProducts($filters);
        
        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findProduct($id);
        
        return $this->resourceResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct(
            array_merge($request->validated(), ['user_id' => 2])
        );

        return $this->resourceResponse(
            new ProductResource($product),
            'Product created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->updateProduct(
            product: $product,
            data: $request->validated()
        );

        return $this->resourceResponse(
            new ProductResource($product),
            'Product updated successfully'
        );
    }
} 