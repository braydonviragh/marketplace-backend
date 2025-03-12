<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    use ApiResponse;

    public function __construct(
        private OfferService $offerService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'offer_status_id' => 'sometimes|exists:offer_status,id',
            'product_id' => 'sometimes|exists:products,id',
            'date_from' => 'sometimes|date_format:Y-m-d H:i:s',
            'date_to' => 'sometimes|date_format:Y-m-d H:i:s|after:date_from',
        ]);

        // // Add user context to filters
        // if (!auth()->user()->hasRole('super_admin')) {
        //     if (auth()->user()->hasRole('owner')) {
        //         $filters['owner_id'] = auth()->id();
        //     } else {
        //         $filters['user_id'] = auth()->id();
        //     }
        // }
        $filters['user_id'] = Auth::id();

        $offers = $this->offerService->getOffers($filters);
        
        return $this->collectionResponse(OfferResource::collection($offers));
    }

    public function store(CreateOfferRequest $request): JsonResponse
    {
        $data = $request->validated();
        //TODO: Remove this after testing
        $data['user_id'] = Auth::id();
        $offer = $this->offerService->createOffer($data);
        
        return $this->resourceResponse(
            new OfferResource($offer),
            'Offer created successfully',
            201
        );
    }

    public function show(Offer $offer): JsonResponse
    {
        return $this->resourceResponse(
            new OfferResource($offer->load(['user', 'product', 'product.media']))
        );
    }

    public function updateStatus(Request $request, Offer $offer): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|exists:offer_status,slug'
        ]);

        $this->offerService->updateOfferStatus($offer, $data['status']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Offer status updated successfully'
        ]);
    }

    /**
     * Get offers sent by the authenticated user
     */
    public function sentOffers(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'offer_status_id' => 'sometimes|exists:offer_status,id',
            'date_from' => 'sometimes|date_format:Y-m-d H:i:s',
            'date_to' => 'sometimes|date_format:Y-m-d H:i:s|after:date_from',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ]);

        $filters['user_id'] = Auth::id();
        $perPage = $filters['per_page'] ?? 20;
        
        $offers = $this->offerService->getOffers($filters, $perPage);
        
        return $this->collectionResponse(OfferResource::collection($offers));
    }

    /**
     * Get offers received for the authenticated user's products
     */
    public function receivedOffers(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'offer_status_id' => 'sometimes|exists:offer_status,id',
            'date_from' => 'sometimes|date_format:Y-m-d H:i:s',
            'date_to' => 'sometimes|date_format:Y-m-d H:i:s|after:date_from',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ]);

        $filters['owner_id'] = Auth::id();
        $perPage = $filters['per_page'] ?? 20;
        
        $offers = $this->offerService->getOffers($filters, $perPage);
        
        return $this->collectionResponse(OfferResource::collection($offers));
    }
} 