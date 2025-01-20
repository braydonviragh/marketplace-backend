<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferStatus;
use App\Models\RentalStatus;
use App\Repositories\OfferRepository;
use App\Repositories\RentalRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\OfferCreated;
use App\Events\OfferStatusChanged;
use Illuminate\Support\Facades\DB;

class OfferService
{
    public function __construct(
        private OfferRepository $offerRepository,
        private RentalRepository $rentalRepository
    ) {}

    public function getOffers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->offerRepository->getFilteredOffers($filters, $perPage);
    }

    public function createOffer(array $data): Offer
    {
        // Set initial status to pending
        $data['offer_status_id'] = OfferStatus::where('slug', 'pending')->first()->id;
        
        $offer = $this->offerRepository->create($data);
        
        event(new OfferCreated($offer));

        return $offer->load(['offerStatus', 'product', 'user']);
    }

    public function updateOfferStatus(Offer $offer, string $status): bool
    {
        DB::transaction(function () use ($offer, $status) {
            // Update offer status
            $this->offerRepository->updateStatus($offer, $status);
            
            // If offer is accepted, create a rental
            if ($status === 'accepted') {
                $this->offerRepository->createRentalFromOffer($offer);
            }
        });

        event(new OfferStatusChanged($offer, $status));
        
        return true;
    }
} 