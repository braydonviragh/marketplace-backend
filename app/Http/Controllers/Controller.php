<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function collectionResponse(ResourceCollection $collection, string $message = ''): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $collection->response()->getData()->data,
            'meta' => $collection->response()->getData()->meta ?? null,
        ]);
    }

    protected function resourceResponse(JsonResource $resource, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $resource
        ], $code);
    }
} 