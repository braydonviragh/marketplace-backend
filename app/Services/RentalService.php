<?php

namespace App\Services;

use App\Models\Rental;
use App\Repositories\RentalRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RentalService
{
    protected RentalRepository $repository;

    public function __construct(RentalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getRentals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findRental(int $id): Rental
    {
        $rental = $this->repository->find($id);
        
        if (!$rental) {
            throw new ModelNotFoundException("Rental not found");
        }
        
        return $rental->load(['renter', 'owner', 'listing', 'payments']);
    }

    public function createRental(array $data): Rental
    {
        return DB::transaction(function () use ($data) {
            // Calculate pricing
            $pricing = $this->calculateRentalPricing($data);
            $data = array_merge($data, $pricing);
            
            $rental = $this->repository->create($data);
            
            // Create initial payment record
            $this->createInitialPayment($rental);
            
            // Notify owner of new rental request
            event(new RentalRequested($rental));
            
            return $rental->load(['renter', 'owner', 'listing', 'payments']);
        });
    }

    public function updateRental(Rental $rental, array $data): Rental
    {
        return DB::transaction(function () use ($rental, $data) {
            // If dates are being updated, recalculate pricing
            if (isset($data['start_date']) || isset($data['end_date'])) {
                $pricing = $this->calculateRentalPricing($data);
                $data = array_merge($data, $pricing);
            }
            
            $rental = $this->repository->update($rental, $data);
            
            // Notify parties of updates
            event(new RentalUpdated($rental));
            
            return $rental->load(['renter', 'owner', 'listing', 'payments']);
        });
    }

    public function deleteRental(Rental $rental): bool
    {
        return DB::transaction(function () use ($rental) {
            // Handle cancellation logic
            if ($rental->status !== 'cancelled') {
                $this->handleCancellation($rental);
            }
            
            event(new RentalDeleted($rental));
            return $this->repository->delete($rental);
        });
    }

    public function updateStatus(Rental $rental, string $newStatus, ?string $reason = null): Rental
    {
        return DB::transaction(function () use ($rental, $newStatus, $reason) {
            $oldStatus = $rental->status;
            
            $rental->updateStatus($newStatus, $reason);
            
            // Handle status-specific logic
            match ($newStatus) {
                'confirmed' => $this->handleConfirmation($rental),
                'in_progress' => $this->handlePickup($rental),
                'completed' => $this->handleReturn($rental),
                'cancelled' => $this->handleCancellation($rental),
                'disputed' => $this->handleDispute($rental),
                default => null
            };
            
            event(new RentalStatusChanged($rental, $oldStatus, $newStatus));
            
            return $rental->fresh()->load(['renter', 'owner', 'listing', 'payments']);
        });
    }

    protected function calculateRentalPricing(array $data): array
    {
        $listing = $this->repository->findListing($data['listing_id']);
        $days = now()->parse($data['start_date'])->diffInDays($data['end_date']);
        
        // Calculate based on duration and pricing tiers
        $totalPrice = $this->calculateTotalPrice($listing, $days);
        $platformFee = $totalPrice * 0.10; // 10% platform fee
        $ownerEarnings = $totalPrice - $platformFee;
        
        return [
            'total_price' => $totalPrice,
            'platform_fee' => $platformFee,
            'owner_earnings' => $ownerEarnings
        ];
    }

    protected function createInitialPayment(Rental $rental): void
    {
        $rental->payments()->create([
            'payer_id' => $rental->renter_id,
            'payee_id' => $rental->owner_id,
            'amount' => $rental->total_price,
            'status' => 'pending',
            'payment_method' => 'pending',
            'currency' => 'CAD'
        ]);
    }

    protected function handleConfirmation(Rental $rental): void
    {
        // Update listing availability
        $rental->listing->markUnavailable($rental->start_date, $rental->end_date);
    }

    protected function handlePickup(Rental $rental): void
    {
        $rental->update(['picked_up_at' => now()]);
    }

    protected function handleReturn(Rental $rental): void
    {
        $rental->update(['returned_at' => now()]);
        $rental->listing->markAvailable($rental->start_date, $rental->end_date);
    }

    protected function handleCancellation(Rental $rental): void
    {
        // Process refund if applicable
        if ($rental->payments()->where('status', 'completed')->exists()) {
            event(new RentalRefundRequired($rental));
        }
        
        // Free up the listing dates
        $rental->listing->markAvailable($rental->start_date, $rental->end_date);
    }

    protected function handleDispute(Rental $rental): void
    {
        // Notify admin team
        event(new RentalDisputed($rental));
    }
} 