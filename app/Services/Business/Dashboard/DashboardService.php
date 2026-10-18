<?php

namespace App\Services\Business\Dashboard;

use App\Models\Business\Beat;
use App\Models\Business\Visit;
use App\Models\Business\Order;
use App\Models\Business\Outlet;
use App\Models\Business\OutletAssignment;

class DashboardService
{
    public function index(?int $userId = null): array
    {
        $userId = $userId ?? authId();
        $today = now()->toDateString();

        $totalOutlets = Outlet::count();
        $totalAssignedOutlets = OutletAssignment::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->count();

        $visitsToday = Visit::whereDate('started_at', $today)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        $visitedCount = $visitsToday->where('verification_status', true)->count();
        $pendingCount = $visitsToday->where('status', 'pending')->count();
        $completedCount = $visitsToday->where('status', 'completed')->count();

        $coveragePercentage = $totalAssignedOutlets > 0 ? round(($visitedCount / $totalAssignedOutlets) * 100, 2) : 0;

        $ordersToday = Order::whereDate('ordered_at', $today)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        $recentVisits = Visit::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->with('outlet')
            ->latest()
            ->limit(5)
            ->get();

        return [
            'total_outlets' => $totalOutlets,
            'assigned_outlets' => $totalAssignedOutlets,
            'visited_today' => $visitedCount,
            'pending_today' => $pendingCount,
            'completed_today' => $completedCount,
            'coverage_percentage' => $coveragePercentage,
            'orders_today_count' => $ordersToday->count(),
            'orders_today_value' => $ordersToday->sum('total_amount'),
            'recent_visits' => $recentVisits,
        ];
    }
}
