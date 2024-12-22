<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->query()
            ->when($filters['user_id'] ?? null, function (Builder $query, int $userId) {
                $query->where('user_id', $userId);
            })
            ->when($filters['type'] ?? null, function (Builder $query, string $type) {
                $query->where('type', $type);
            })
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($filters['unread'] ?? null, function (Builder $query) {
                $query->whereNull('read_at');
            })
            ->when($filters['date_range'] ?? null, function (Builder $query, array $dateRange) {
                $query->whereBetween('created_at', $dateRange);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function markAsRead(int $notificationId): bool
    {
        $notification = $this->find($notificationId);
        return $notification->markAsRead();
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read'
            ]);
    }
} 