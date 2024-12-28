<?php

namespace App\Services;

use App\Models\Rental;
use App\Repositories\RentalRepository;
use App\Exceptions\RentalException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

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

    public function createRental(array $data): Rental
    {
        return $this->rentalRepository->create($data);
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
            throw new RentalException('Only pending or active rentals can be cancelled');
        }

        $success = $this->rentalRepository->updateStatus($rental, 'cancelled');
        
        if ($success) {
            event(new RentalStatusChanged($rental, 'cancelled'));
        }
        
        return $success;
    }
} 