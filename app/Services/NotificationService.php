<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    protected NotificationRepository $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getNotifications(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findNotification(int $id)
    {
        return $this->repository->find($id);
    }

    public function markAsRead(int $notificationId): bool
    {
        return $this->repository->markAsRead($notificationId);
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->repository->markAllAsRead($userId);
    }
} 