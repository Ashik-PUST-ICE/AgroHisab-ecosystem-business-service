<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Notification\NotificationCollection;
use App\Http\Resources\Business\Notification\NotificationResource;
use App\Services\Business\Notification\NotificationService;
use App\Services\Applications\Api\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $notifications = $this->notificationService->index($request->only(['unread_only', 'per_page']));

            return ApiResponse::success(new NotificationCollection($notifications), 'Notifications retrieved successfully');
        });
    }

    public function markAsRead(int $id)
    {
        return $this->handleRequest(function () use ($id) {
            $notification = $this->notificationService->markAsRead($id);

            return ApiResponse::success(new NotificationResource($notification), 'Notification marked as read');
        });
    }

    public function markAllAsRead()
    {
        return $this->handleRequest(function () {
            $this->notificationService->markAllAsRead();

            return ApiResponse::success(null, 'All notifications marked as read');
        });
    }

    public function unreadCount()
    {
        return $this->handleRequest(function () {
            $count = $this->notificationService->unreadCount();

            return ApiResponse::success(['count' => $count], 'Unread count retrieved successfully');
        });
    }
}
