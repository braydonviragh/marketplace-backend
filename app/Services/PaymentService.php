<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentService
{
    protected PaymentRepository $repository;

    public function __construct(PaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPayments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findPayment(int $id)
    {
        return $this->repository->find($id);
    }
} 