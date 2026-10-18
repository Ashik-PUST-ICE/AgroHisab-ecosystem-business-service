<?php

namespace App\Services\Business\Notification;

use App\Models\Business\Notification;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function index(array $filters): LengthAwarePaginator
    {
        $userId = $filters['user_id'] ?? authId();

        return Notification::query()
            ->where('user_id', $userId)
            ->when($filters['unread_only'] ?? null, fn($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Notification
    {
        return Notification::create($data);
    }

    public function markAsRead(int $notificationId): Notification
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', authId())
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return $notification;
    }

    public function markAllAsRead(?int $userId = null): void
    {
        $userId = $userId ?? authId();

        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function unreadCount(?int $userId = null): int
    {
        $userId = $userId ?? authId();

        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function send(string $userId, string $title, string $message, array $data = [], ?string $fcmToken = null): void
    {
        $token = $fcmToken;

        if (! $token) {
            $user = \App\Models\User::find($userId);
            $token = $user->fcm_token ?? null;
        }

        if (! $token) {
            return;
        }

        $this->store([
            'user_id' => $userId,
            'type' => 'fcm',
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        app(FcmService::class)->sendToToken($token, $title, $message, $data);
    }
}
