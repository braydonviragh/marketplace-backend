<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        private OfferService $offerService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status_id' => 'sometimes|exists:offer_status,id',
            'product_id' => 'sometimes|exists:products,id',
            'date_from' => 'sometimes|date_format:Y-m-d H:i:s',
            'date_to' => 'sometimes|date_format:Y-m-d H:i:s|after:date_from',
        ]);

        // Add user context to filters
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->hasRole('owner')) {
                $filters['owner_id'] = auth()->id();
            } else {
                $filters['user_id'] = auth()->id();
            }
        }

        $offers = $this->offerService->getOffers($filters);
        
        return response()->json([
            'data' => OfferResource::collection($offers)
        ]);
    }

    public function store(CreateOfferRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $offer = $this->offerService->createOffer($data);
        
        return response()->json([
            'message' => 'Offer created successfully',
            'data' => new OfferResource($offer)
        ], 201);
    }

    public function show(Offer $offer): JsonResponse
    {
        return response()->json([
            'data' => new OfferResource($offer->load(['product', 'user', 'offerStatus']))
        ]);
    }

    public function updateStatus(Request $request, Offer $offer): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|exists:offer_status,slug'
        ]);

        $this->offerService->updateOfferStatus($offer, $data['status']);
        
        return response()->json([
            'message' => 'Offer status updated successfully',
            'data' => new OfferResource($offer->fresh(['product', 'user', 'offerStatus']))
        ]);
    }
} 