<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Listing;
use App\Services\ListingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListingRequest;
use App\Http\Resources\ListingResource;
use App\Http\Resources\ListingCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ListingController extends Controller
{
    protected ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
        $this->middleware('auth:sanctum')->except(['index', 'show', 'search']);
        $this->middleware('verify.listing.owner')->only(['update', 'destroy']);
    }

    public function index(ListingRequest $request): JsonResponse
    {
        $listings = $this->listingService->getListings(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new ListingCollection($listings),
            'Listings retrieved successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $listing = $this->listingService->findListing($id);
        
        return $this->resourceResponse(
            new ListingResource($listing),
            'Listing retrieved successfully'
        );
    }

    public function store(ListingRequest $request): JsonResponse
    {
        $listing = $this->listingService->createListing(
            data: array_merge($request->validated(), ['user_id' => auth()->id()]),
            images: $request->file('images', [])
        );

        return $this->resourceResponse(
            new ListingResource($listing),
            'Listing created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(ListingRequest $request, Listing $listing): JsonResponse
    {
        $listing = $this->listingService->updateListing(
            listing: $listing,
            data: $request->validated(),
            images: $request->file('images', [])
        );

        return $this->resourceResponse(
            new ListingResource($listing),
            'Listing updated successfully'
        );
    }

    public function destroy(Listing $listing): JsonResponse
    {
        $this->listingService->deleteListing($listing);

        return response()->json([
            'message' => 'Listing deleted successfully'
        ]);
    }

    public function search(ListingRequest $request): JsonResponse
    {
        $listings = $this->listingService->searchNearby(
            latitude: $request->latitude,
            longitude: $request->longitude,
            radius: $request->radius ?? 25,
            filters: $request->validated()
        );

        return $this->collectionResponse(
            new ListingCollection($listings),
            'Listings found successfully'
        );
    }
} 