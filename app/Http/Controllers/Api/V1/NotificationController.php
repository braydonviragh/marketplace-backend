<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\NotificationService;
use App\Http\Requests\NotificationRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotificationCollection;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(NotificationRequest $request)
    {
        $notifications = $this->notificationService->getNotifications(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new NotificationCollection($notifications),
            'Notifications retrieved successfully'
        );
    }

    public function markAsRead(int $id)
    {
        $this->notificationService->markAsRead($id);
        
        return $this->successResponse(
            data: null,
            message: 'Notification marked as read',
            code: 200
        );
    }

    public function markAllAsRead(NotificationRequest $request)
    {
        $this->notificationService->markAllAsRead($request->user()->id);
        
        return $this->successResponse(
            data: null,
            message: 'All notifications marked as read',
            code: 200
        );
    }
} 