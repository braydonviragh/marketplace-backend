<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    protected function successResponse($data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    protected function resourceResponse(JsonResource $resource, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $resource
        ], $code);
    }

    protected function collectionResponse(ResourceCollection $collection, string $message = ''): JsonResponse
    {
        $response = $collection->response()->getData(true);
        
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $response['data'],
            'meta' => $response['meta'] ?? null,
            'links' => $response['links'] ?? null,
        ]);
    }
} 