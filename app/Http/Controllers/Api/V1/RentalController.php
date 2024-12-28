<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;

class RentalController extends Controller
{
    public function __construct(
        private RentalService $rentalService
    ) {}

    public function index(): JsonResponse
    {
        $rentals = $this->rentalService->getRentals();
        return response()->json([
            'data' => RentalResource::collection($rentals)
        ]);
    }   

    public function store(CreateRentalRequest $request): JsonResponse
    {
        $rental = $this->rentalService->createRental($request->validated());
        
        return response()->json([
            'message' => 'Rental created successfully',
            'data' => new RentalResource($rental)
        ], 201);
    }

    public function show(Rental $rental): JsonResponse
    {
        return response()->json([
            'data' => new RentalResource($rental)
        ]);
    }

    public function activate(Rental $rental): JsonResponse
    {
        $this->rentalService->activateRental($rental);
        
        return response()->json([
            'message' => 'Rental activated successfully',
            'data' => new RentalResource($rental)
        ]);
    }

    public function complete(Rental $rental): JsonResponse
    {
        $this->rentalService->completeRental($rental);
        
        return response()->json([
            'message' => 'Rental completed successfully',
            'data' => new RentalResource($rental)
        ]);
    }

    public function cancel(Rental $rental): JsonResponse
    {
        $this->rentalService->cancelRental($rental);
        
        return response()->json([
            'message' => 'Rental cancelled successfully',
            'data' => new RentalResource($rental)
        ]);
    }
} 