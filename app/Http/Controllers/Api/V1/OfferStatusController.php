<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferStatusResource;
use App\Models\OfferStatus;
use Illuminate\Http\JsonResponse;

class OfferStatusController extends Controller
{
    /**
     * Get all offer statuses
     */
    public function index(): JsonResponse
    {
        $statuses = OfferStatus::select(['id', 'name', 'slug', 'description'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => OfferStatusResource::collection($statuses)
        ]);
    }
} 