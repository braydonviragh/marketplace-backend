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
use App\Models\Payment;

class OfferService
{
    public function __construct(
        private OfferRepository $offerRepository,
        private RentalRepository $rentalRepository,
        private StripeService $stripeService
    ) {}

    public function getOffers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Offer::query()
            ->with(['product.media', 'user', 'offerStatus']);

        if (isset($filters['offer_status_id'])) {
            $query->where('offer_status_id', $filters['offer_status_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['owner_id'])) {
            $query->whereHas('product', function ($query) use ($filters) {
                $query->where('user_id', $filters['owner_id']);
            });
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createOffer(array $data): Offer
    {
        // Set initial status to pending
        $data['offer_status_id'] = OfferStatus::where('slug', 'pending')->first()->id;
        
        $offer = $this->offerRepository->create($data);
        $offer->load(['product.media', 'user', 'offerStatus']);

        //TODO: Work on this after adding notifications
        // Dispatch the OfferCreated event
        // event(new OfferCreated($offer));
        
        return $offer;
    }

    public function updateOfferStatus(Offer $offer, string $status): bool
    {
        DB::transaction(function () use ($offer, $status) {
            // Update offer status
            $this->offerRepository->updateStatus($offer, $status);
            
            // If offer is accepted, create a rental and initiate payment
            if ($status === 'accepted') {
                // Create rental
                $rental = $this->offerRepository->createRentalFromOffer($offer);
                
                // Create payment intent
                $paymentIntent = $this->stripeService->createPaymentIntent($rental);
                
                if (!$paymentIntent['success']) {
                    throw new \Exception('Failed to create payment intent: ' . ($paymentIntent['error'] ?? 'Unknown error'));
                }
                
                // Create a pending payment record
                Payment::create([
                    'rental_id' => $rental->id,
                    'amount' => $rental->offer->product->price,
                    'status' => 'pending',
                    'payment_method' => 'stripe',
                    'stripe_payment_intent_id' => $paymentIntent['payment_intent_id']
                ]);
            }
        });

        event(new OfferStatusChanged($offer, $status));
        
        return true;
    }
} 