<?php

namespace App\Repositories;

use App\Models\Offer;
use App\Models\OfferStatus;
use App\Models\RentalStatus;
use App\Models\Rental;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OfferRepository
{
    public function __construct(
        private Offer $model
    ) {}

    public function getFilteredOffers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'product', 'offerStatus']);

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by product owner
        if (isset($filters['owner_id'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('user_id', $filters['owner_id']);
            });
        }

        // Filter by status
        if (isset($filters['status_id'])) {
            $query->where('offer_status_id', $filters['status_id']);
        }

        // Filter by product
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Offer
    {
        return $this->model->create($data);
    }

    public function updateStatus(Offer $offer, string $statusSlug): bool
    {
        $status = OfferStatus::where('slug', $statusSlug)->firstOrFail();
        return $offer->update(['offer_status_id' => $status->id]);
    }

    public function createRentalFromOffer(Offer $offer): Rental
    {
        // Get pending rental status
        $pendingStatus = RentalStatus::where('slug', 'pending')->firstOrFail();

        // Create rental from offer
        return Rental::create([
            'offer_id' => $offer->id,
            'rental_status_id' => $pendingStatus->id
        ]);
    }

    public function hasActiveRental(Offer $offer): bool
    {
        return $offer->rental()
            ->whereHas('rentalStatus', function($query) {
                $query->whereIn('slug', ['pending', 'active']);
            })
            ->exists();
    }
} 