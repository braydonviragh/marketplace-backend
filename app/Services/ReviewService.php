<?php

namespace App\Services;

use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReviewService
{
    protected ReviewRepository $repository;

    public function __construct(ReviewRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getReviews(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findReview(int $id): Review
    {
        $review = $this->repository->find($id);
        
        if (!$review) {
            throw new ModelNotFoundException("Review not found");
        }
        
        return $review->load(['reviewer', 'reviewee', 'rental']);
    }

    public function createReview(array $data): Review
    {
        return DB::transaction(function () use ($data) {
            $review = $this->repository->create($data);
            
            // Notify the reviewee
            event(new ReviewCreated($review));
            
            return $review->load(['reviewer', 'reviewee', 'rental']);
        });
    }

    public function updateReview(Review $review, array $data): Review
    {
        return DB::transaction(function () use ($review, $data) {
            $review = $this->repository->update($review, $data);
            
            // Notify if review was moderated
            if (isset($data['is_approved'])) {
                event(new ReviewModerated($review));
            }
            
            return $review->load(['reviewer', 'reviewee', 'rental']);
        });
    }

    public function deleteReview(Review $review): bool
    {
        return DB::transaction(function () use ($review) {
            event(new ReviewDeleted($review));
            return $this->repository->delete($review);
        });
    }
} 