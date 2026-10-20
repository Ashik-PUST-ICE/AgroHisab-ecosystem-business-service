<?php

namespace App\Http\Controllers\Api\V1\Business\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Notification\NotificationResource;
use App\Models\Business\Notification;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * GET /api/v1/notifications
     * Query params: unread_only (bool), per_page (int)
     */
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $notifications = $this->notificationService->list(
                $request->only(['unread_only', 'per_page'])
            );
            return ApiResponse::success(
                NotificationResource::collection($notifications),
                'Notifications retrieved successfully',
                200,
                ['unread_count' => $this->notificationService->unreadCount()]
            );
        });
    }

    /**
     * POST /api/v1/notifications/{notification}/read
     */
    public function markRead(Notification $notification)
    {
        return $this->handleRequest(function () use ($notification) {
            $notification = $this->notificationService->markRead($notification);
            return ApiResponse::success(new NotificationResource($notification), 'Notification marked as read');
        });
    }

    /**
     * POST /api/v1/notifications/read-all
     */
    public function markAllRead()
    {
        return $this->handleRequest(function () {
            $count = $this->notificationService->markAllRead();
            return ApiResponse::success(['updated' => $count], "{$count} notifications marked as read");
        });
    }
}