<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Offer;
use App\Services\OfferService;
use App\Http\Controllers\Controller;
use App\Http\Requests\OfferRequest;
use App\Http\Resources\OfferResource;
use App\Http\Resources\OfferCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OfferController extends Controller
{
    protected OfferService $offerService;

    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function index(OfferRequest $request): JsonResponse
    {
        $offers = $this->offerService->getOffers(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new OfferCollection($offers),
            'Offers retrieved successfully'
        );
    }

    public function store(OfferRequest $request): JsonResponse
    {
        $offer = $this->offerService->createOffer(
            array_merge($request->validated(), ['user_id' => auth()->id()])
        );

        return $this->resourceResponse(
            new OfferResource($offer),
            'Offer created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(OfferRequest $request, Offer $offer): JsonResponse
    {
        $offer = $this->offerService->updateOffer(
            offer: $offer,
            data: $request->validated()
        );

        return $this->resourceResponse(
            new OfferResource($offer),
            'Offer updated successfully'
        );
    }

    public function accept(Offer $offer): JsonResponse
    {
        $offer = $this->offerService->acceptOffer($offer);

        return $this->resourceResponse(
            new OfferResource($offer),
            'Offer accepted successfully'
        );
    }

    public function reject(OfferRequest $request, Offer $offer): JsonResponse
    {
        $offer = $this->offerService->rejectOffer(
            offer: $offer,
            reason: $request->reason
        );

        return $this->resourceResponse(
            new OfferResource($offer),
            'Offer rejected successfully'
        );
    }
} 