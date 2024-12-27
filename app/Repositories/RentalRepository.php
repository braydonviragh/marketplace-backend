<?php

namespace App\Repositories;

use App\Models\Rental;
use Illuminate\Database\Eloquent\Collection;

class RentalRepository
{
    public function create(array $data): Rental
    {
        return Rental::create($data);
    }

    public function update(Rental $rental, array $data): bool
    {
        return $rental->update($data);
    }

    public function getUserRentals(int $userId): Collection
    {
        return Rental::where('user_id', $userId)
            ->with(['product', 'payment'])
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Rental
    {
        return Rental::with(['product', 'user', 'payment'])->find($id);
    }

    public function updateStatus(Rental $rental, string $status): bool
    {
        return $rental->update(['status' => $status]);
    }
}