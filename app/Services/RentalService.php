<?php

namespace App\Services;

use App\Models\Rental;
use App\Repositories\RentalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\RentalCreated;
use App\Events\RentalStatusChanged;
use Carbon\Carbon;
use App\Models\RentalConfirmation;
use App\Models\UserTransaction;
use App\Models\AppTransaction;
use App\Models\AppBalance;
use App\Models\RentalStatus;
use Illuminate\Support\Facades\DB;

class RentalService
{
    public function __construct(
        private RentalRepository $rentalRepository
    ) {}

    public function getRentals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->rentalRepository->getFilteredRentals($filters, $perPage);
    }

    public function getUserRentals(int $userId): Collection
    {
        return $this->rentalRepository->getUserRentals($userId);
    }

    /**
     * Create a new rental with pending status
     */
    public function createRental(array $data): Rental
    {
        // Get the 'pending' status (ID 1)
        $data['rental_status_id'] = 1;
        
        $rental = $this->rentalRepository->create($data);

        event(new RentalCreated($rental));

        return $rental->load(['rentalStatus', 'product', 'user']);
    }

    public function activateRental(Rental $rental): bool
    {
        DB::transaction(function () use ($rental) {
            // Calculate owner amount (80%)
            // Calculate total rental price
            $totalPrice = $rental->product->price;
            
            // Calculate owner's share (80%)
            $amount = $totalPrice * 0.80;
            
            // Calculate app fee (20%) 
            $appFee = $totalPrice * 0.20;
            // Create owner transaction

            UserTransaction::create([
                'user_id' => $rental->product->user_id,
                'rental_id' => $rental->id,
                'amount' => $amount,
                'type' => 'add'
            ]);
            
            // Create app transaction
            AppTransaction::create([
                'rental_id' => $rental->id,
                'amount' => $appFee,
            ]);

            // Get current balance
            $currentBalance = AppBalance::getCurrentBalance();

            // Add fee to balance
            AppBalance::addToBalance($appFee);

            // Update rental status
            $rental->update(['rental_status_id' => RentalStatus::where('slug', 'active')->first()->id]);
        });

        event(new RentalStatusChanged($rental, 'active'));
        
        return true;
    }

    public function completeRental(Rental $rental): bool
    {
        if ($rental->status !== 'active') {
            throw new \Exception('Only active rentals can be completed');
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
            throw new \Exception('Only pending or active rentals can be cancelled');
        }

        $success = $this->rentalRepository->updateStatus($rental, 'cancelled');
        
        if ($success) {
            event(new RentalStatusChanged($rental, 'cancelled'));
        }
        
        return $success;
    }

    public function confirmRental(Rental $rental): void
    {
        $userId = 2; //auth()->id();
        // Determine if user is renter or owner
        if ($rental->user_id === $userId) {
            $userType = 'renter';
        } elseif ($rental->product->user_id === $userId) {
            $userType = 'owner';
        } else {
            throw new RentalException('You are not authorized to confirm this rental.');
        }

        // Check if rental is in pending status
        if ($rental->rentalStatus->slug !== 'pending') {
            throw new \Exception('Only pending rentals can be confirmed.');
        }

        // Check if user hasn't already confirmed
        $existingConfirmation = RentalConfirmation::where([
            'rental_id' => $rental->id,
            'user_id' => $userId
        ])->exists();

        if ($existingConfirmation) {
            throw new \Exception('You have already confirmed this rental.');
        }

        // Record the confirmation
        RentalConfirmation::create([
            'rental_id' => $rental->id,
            'user_id' => $userId,
            'type' => $userType
        ]);

        // Check if both parties have confirmed
        $confirmations = RentalConfirmation::where('rental_id', $rental->id)->count();
        
        if ($confirmations === 2) {
            // Activate the rental
            $this->activateRental($rental);
        }
    }
} 