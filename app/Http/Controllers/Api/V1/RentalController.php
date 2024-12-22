<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Rental;
use App\Services\RentalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\RentalRequest;
use App\Http\Resources\RentalResource;
use App\Http\Resources\RentalCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RentalController extends Controller
{
    protected RentalService $rentalService;

    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
        $this->middleware('auth:sanctum');
        $this->middleware('verify.rental.participant')->except(['index', 'store']);
    }

    public function index(RentalRequest $request): JsonResponse
    {
        $rentals = $this->rentalService->getRentals(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new RentalCollection($rentals),
            'Rentals retrieved successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $rental = $this->rentalService->findRental($id);
        
        return $this->resourceResponse(
            new RentalResource($rental),
            'Rental retrieved successfully'
        );
    }

    public function store(RentalRequest $request): JsonResponse
    {
        $rental = $this->rentalService->createRental(
            array_merge($request->validated(), ['renter_id' => auth()->id()])
        );

        return $this->resourceResponse(
            new RentalResource($rental),
            'Rental created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(RentalRequest $request, Rental $rental): JsonResponse
    {
        $rental = $this->rentalService->updateRental(
            rental: $rental,
            data: $request->validated()
        );

        return $this->resourceResponse(
            new RentalResource($rental),
            'Rental updated successfully'
        );
    }

    public function destroy(Rental $rental): JsonResponse
    {
        $this->rentalService->deleteRental($rental);

        return response()->json([
            'message' => 'Rental deleted successfully'
        ]);
    }

    public function updateStatus(RentalRequest $request, Rental $rental): JsonResponse
    {
        $rental = $this->rentalService->updateStatus(
            rental: $rental,
            newStatus: $request->status,
            reason: $request->reason
        );

        return $this->resourceResponse(
            new RentalResource($rental),
            'Rental status updated successfully'
        );
    }
} 