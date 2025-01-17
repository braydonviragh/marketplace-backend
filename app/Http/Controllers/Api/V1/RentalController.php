<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\JsonResponse;
use App\Models\RentalStatus;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function __construct(
        private RentalService $rentalService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status_id' => 'sometimes|exists:rental_status,id',
            'product_id' => 'sometimes|exists:products,id',
            'date_from' => 'sometimes|date_format:Y-m-d H:i:s',
            'date_to' => 'sometimes|date_format:Y-m-d H:i:s|after:date_from',
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id' => 'sometimes|exists:brands,id',
            'color_id' => 'sometimes|exists:colors,id',
            'letter_size_id' => 'sometimes|exists:letter_sizes,id',
            'number_size_id' => 'sometimes|exists:number_sizes,id',
            'waist_size_id' => 'sometimes|exists:waist_sizes,id',
            'price_min' => 'sometimes|numeric|min:0',
            'price_max' => 'sometimes|numeric|gt:price_min',
            'sort_by' => 'sometimes|in:price_asc,price_desc,date_asc,date_desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        // Add authenticated user's ID to filters unless they're a super admin
        // if (!auth()->user()->hasRole('super_admin')) {
        //     $filters['user_id'] = auth()->id();
        // }

        $rentals = $this->rentalService->getRentals($filters);
        
        return response()->json([
            'data' => RentalResource::collection($rentals)
        ]);
    }   

    public function store(CreateRentalRequest $request): JsonResponse
    {
        $data = $request->validated();
        // $data['user_id'] = auth()->id();

        //TODO: Remove this after testing
        $data['user_id'] = 1;
        $data['rental_status_id'] = RentalStatus::where('slug', 'pending')->first()->id;

        $rental = $this->rentalService->createRental($data);
        
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

    public function confirm(Rental $rental): JsonResponse
    {
        $this->rentalService->confirmRental($rental);
        
        return response()->json([
            'message' => 'Rental confirmation recorded successfully',
            'data' => new RentalResource($rental)
        ]);
    }
} 