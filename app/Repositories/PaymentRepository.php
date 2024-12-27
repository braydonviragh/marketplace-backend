<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['payer', 'payee', 'rental'])
            ->when($filters['user_id'] ?? null, function (Builder $query, int $userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('payer_id', $userId)
                      ->orWhere('payee_id', $userId);
                });
            })
            ->when($filters['rental_id'] ?? null, function (Builder $query, int $rentalId) {
                $query->where('rental_id', $rentalId);
            })
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($filters['payment_method'] ?? null, function (Builder $query, string $method) {
                $query->where('payment_method', $method);
            })
            ->when($filters['date_range'] ?? null, function (Builder $query, array $dateRange) {
                $query->whereBetween('created_at', $dateRange);
            })
            ->latest()
            ->paginate($perPage);
    }
} 