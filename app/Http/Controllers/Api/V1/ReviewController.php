<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Review;
use App\Services\ReviewService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ReviewCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('verify.review.owner')->only(['update', 'destroy']);
    }

    public function index(ReviewRequest $request): JsonResponse
    {
        $reviews = $this->reviewService->getReviews(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new ReviewCollection($reviews),
            'Reviews retrieved successfully'
        );
    }

    public function show(int $id): JsonResponse
    {
        $review = $this->reviewService->findReview($id);
        
        return $this->resourceResponse(
            new ReviewResource($review),
            'Review retrieved successfully'
        );
    }

    public function store(ReviewRequest $request): JsonResponse
    {
        $review = $this->reviewService->createReview(
            array_merge($request->validated(), ['reviewer_id' => auth()->id()])
        );

        return $this->resourceResponse(
            new ReviewResource($review),
            'Review created successfully',
            Response::HTTP_CREATED
        );
    }

    public function update(ReviewRequest $request, Review $review): JsonResponse
    {
        $review = $this->reviewService->updateReview(
            review: $review,
            data: $request->validated()
        );

        return $this->resourceResponse(
            new ReviewResource($review),
            'Review updated successfully'
        );
    }

    public function destroy(Review $review): JsonResponse
    {
        $this->reviewService->deleteReview($review);

        return response()->json([
            'message' => 'Review deleted successfully'
        ]);
    }
} 