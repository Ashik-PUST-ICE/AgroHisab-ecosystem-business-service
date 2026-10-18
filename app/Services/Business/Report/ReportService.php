<?php

namespace App\Services\Business\Report;

use App\Models\Business\Visit;
use App\Models\Business\Order;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function visits(array $filters): array
    {
        $query = Visit::query()
            ->when($filters['from'] ?? null, fn($q, $from) => $q->whereDate('started_at', '>=', $from))
            ->when($filters['to'] ?? null, fn($q, $to) => $q->whereDate('started_at', '<=', $to))
            ->when($filters['user_id'] ?? null, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['outlet_id'] ?? null, fn($q, $outletId) => $q->where('outlet_id', $outletId));

        return [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];
    }

    public function orders(array $filters): array
    {
        $query = Order::query()
            ->when($filters['from'] ?? null, fn($q, $from) => $q->whereDate('ordered_at', '>=', $from))
            ->when($filters['to'] ?? null, fn($q, $to) => $q->whereDate('ordered_at', '<=', $to))
            ->when($filters['user_id'] ?? null, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['outlet_id'] ?? null, fn($q, $outletId) => $q->where('outlet_id', $outletId));

        return [
            'total' => (clone $query)->count(),
            'total_amount' => (clone $query)->sum('total_amount'),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
        ];
    }

    public function officerPerformance(array $filters): array
    {
        $query = Visit::query()
            ->select('user_id', DB::raw('count(*) as total_visits'))
            ->when($filters['from'] ?? null, fn($q, $from) => $q->whereDate('started_at', '>=', $from))
            ->when($filters['to'] ?? null, fn($q, $to) => $q->whereDate('started_at', '<=', $to))
            ->groupBy('user_id')
            ->get();

        return $query->toArray();
    }
}
