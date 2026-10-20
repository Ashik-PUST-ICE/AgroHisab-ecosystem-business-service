<?php

namespace App\Services\Business\Notification;

use App\Models\Business\Notification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Notification::where('user_id', authId());

        if (!empty($filters['unread_only']) && filter_var($filters['unread_only'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('read_at');
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        return $query->latest('id')->paginate($perPage);
    }

    public function unreadCount(): int
    {
        return Notification::where('user_id', authId())->whereNull('read_at')->count();
    }

    public function markRead(Notification $notification): Notification
    {
        if (!$notification->read_at) {
            $notification->update(['read_at' => Carbon::now()]);
        }
        return $notification->fresh();
    }

    public function markAllRead(): int
    {
        return Notification::where('user_id', authId())
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }
}