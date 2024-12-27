<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\Offer;
use App\Repositories\RentalRepository;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Events\RentalCreated;
use App\Events\RentalStatusChanged;
use App\Exceptions\RentalException;

class RentalService
{
    public function __construct(
        private RentalRepository $rentalRepository,
        private PaymentService $paymentService
    ) {}

    public function createFromOffer(Offer $offer, array $paymentData): Rental
    {
        return DB::transaction(function () use ($offer, $paymentData) {
            // Validate offer status
            if ($offer->status !== 'accepted') {
                throw new RentalException('Cannot create rental from non-accepted offer');
            }

            // Process payment first
            $payment = $this->paymentService->processPayment([
                'amount' => $offer->amount,
                'payment_data' => $paymentData
            ]);
            
            $rentalData = [
                'product_id' => $offer->product_id,
                'user_id' => $offer->user_id,
                'payment_id' => $payment->id,
                'rental_from' => Carbon::now(),
                'rental_to' => Carbon::now()->addDays(7), // Default rental period
                'status' => 'pending',
                'offer_id' => $offer->id
            ];

            $rental = $this->rentalRepository->create($rentalData);
            
            // Update offer status
            $offer->update(['status' => 'converted']);
            
            // Dispatch rental created event
            event(new RentalCreated($rental));
            
            return $rental;
        });
    }

    public function activateRental(Rental $rental): bool
    {
        $success = $this->rentalRepository->updateStatus($rental, 'active');
        
        if ($success) {
            event(new RentalStatusChanged($rental, 'active'));
        }
        
        return $success;
    }

    public function completeRental(Rental $rental): bool
    {
        if ($rental->status !== 'active') {
            throw new RentalException('Only active rentals can be completed');
        }

        $success = $this->rentalRepository->updateStatus($rental, 'completed');
        
        if ($success) {
            event(new RentalStatusChanged($rental, 'completed'));
        }
        
        return $success;
    }

    public function cancelRental(Rental $rental): bool
    {
        if (!in_array($rental->status, ['pending', 'active'])) {
            throw new RentalException('This rental cannot be cancelled');
        }

        return DB::transaction(function () use ($rental) {
            // Process refund if needed
            if ($rental->status === 'active') {
                $this->paymentService->processRefund($rental->payment);
            }

            $success = $this->rentalRepository->updateStatus($rental, 'cancelled');
            
            if ($success) {
                event(new RentalStatusChanged($rental, 'cancelled'));
            }
            
            return $success;
        });
    }
} 