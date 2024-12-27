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
        $products = $this->productService->getProducts(
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new ProductCollection($products),
            'Products retrieved successfully'
        );
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
            array_merge($request->validated(), ['user_id' => auth()->id()])
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