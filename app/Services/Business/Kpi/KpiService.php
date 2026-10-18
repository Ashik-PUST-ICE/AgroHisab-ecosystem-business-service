<?php

namespace App\Services\Business\Kpi;

use App\Models\Business\KpiTarget;
use App\Models\Business\Order;
use App\Models\Business\OutletAssignment;
use App\Models\Business\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function index(array $filters)
    {
        $perPage = $filters['per_page'] ?? 15;

        return KpiTarget::query()
            ->when($filters['user_id'] ?? null, fn($q, $uid) => $q->where('user_id', $uid))
            ->when($filters['period_type'] ?? null, fn($q, $type) => $q->where('period_type', $type))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data): KpiTarget
    {
        return KpiTarget::create($data);
    }

    public function show(KpiTarget $target): KpiTarget
    {
        return $target;
    }

    public function update(KpiTarget $target, array $data): KpiTarget
    {
        $target->update($data);

        return $target->fresh();
    }

    public function destroy(KpiTarget $target): void
    {
        $target->delete();
    }

    public function summary(array $filters): array
    {
        $userId = $filters['user_id'] ?? authId();
        $period = $filters['period'] ?? 'today';

        $now = Carbon::now();
        if ($period === 'this_week') {
            $startDate = $now->copy()->startOfWeek();
            $endDate = $now->copy()->endOfWeek();
            $periodType = 'weekly';
        } elseif ($period === 'this_month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
            $periodType = 'monthly';
        } else {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
            $periodType = 'daily';
        }

        // Active Target lookup or defaults
        $target = KpiTarget::where('status', 'active')
            ->where('period_type', $periodType)
            ->when($userId, fn($q) => $q->where(fn($sub) => $sub->where('user_id', $userId)->orWhereNull('user_id')))
            ->latest()
            ->first();

        $defaultVisits = $periodType === 'daily' ? 15 : ($periodType === 'weekly' ? 90 : 350);
        $defaultSales = $periodType === 'daily' ? 50000.0 : ($periodType === 'weekly' ? 300000.0 : 1200000.0);
        $defaultCoverage = 85.0;
        $defaultStrikeRate = 65.0;

        $visitTarget = $target ? (int) $target->visit_target : $defaultVisits;
        $salesTarget = $target ? (float) $target->order_amount_target : $defaultSales;
        $coverageTarget = $target ? (float) $target->coverage_target_percentage : $defaultCoverage;
        $strikeRateTarget = $defaultStrikeRate;

        // Actual Visits
        $visitsQuery = Visit::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereBetween('started_at', [$startDate, $endDate]);

        $visits = $visitsQuery->get();
        $totalVisits = $visits->count();
        $completedVisits = $visits->where('status', 'completed')->count();
        $verifiedVisits = $visits->where('verification_status', true)->count();

        // Actual Orders
        $ordersQuery = Order::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereBetween('ordered_at', [$startDate, $endDate]);

        $orders = $ordersQuery->get();
        $ordersCount = $orders->count();
        $ordersAmount = (float) $orders->sum('total_amount');

        // Outlet Coverage
        $totalAssigned = OutletAssignment::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->count();

        $uniqueOutletsVisited = $visits->pluck('outlet_id')->unique()->filter()->count();
        $coverageActual = $totalAssigned > 0 ? round(($uniqueOutletsVisited / $totalAssigned) * 100, 1) : 0.0;

        // Efficiency metrics
        $strikeRateActual = $completedVisits > 0 ? round(($ordersCount / $completedVisits) * 100, 1) : 0.0;
        $avgOrderValue = $ordersCount > 0 ? round($ordersAmount / $ordersCount, 2) : 0.0;

        // Achievements
        $visitPercent = $visitTarget > 0 ? round(($completedVisits / $visitTarget) * 100, 1) : 0.0;
        $salesPercent = $salesTarget > 0 ? round(($ordersAmount / $salesTarget) * 100, 1) : 0.0;
        $coveragePercent = $coverageTarget > 0 ? round(($coverageActual / $coverageTarget) * 100, 1) : 0.0;

        // Standing Grade
        $overallScore = round(($visitPercent * 0.4) + ($salesPercent * 0.4) + ($coveragePercent * 0.2), 1);
        if ($overallScore >= 90) {
            $grade = 'Star Performer (A+)';
        } elseif ($overallScore >= 75) {
            $grade = 'On Track (A)';
        } elseif ($overallScore >= 50) {
            $grade = 'Moderate (B)';
        } else {
            $grade = 'Needs Acceleration (C)';
        }

        // Leaderboard
        $leaderboard = Visit::query()
            ->select('user_id', DB::raw('count(*) as total_visits'))
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total_visits')
            ->limit(10)
            ->get();

        return [
            'period' => $period,
            'period_type' => $periodType,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'target' => [
                'id' => $target?->id,
                'title' => $target?->title ?? 'Standard Performance Goal',
                'visit_target' => $visitTarget,
                'sales_target' => $salesTarget,
                'coverage_target' => $coverageTarget,
                'strike_rate_target' => $strikeRateTarget,
            ],
            'actual' => [
                'total_visits' => $totalVisits,
                'completed_visits' => $completedVisits,
                'verified_visits' => $verifiedVisits,
                'orders_count' => $ordersCount,
                'orders_amount' => $ordersAmount,
                'total_assigned_outlets' => $totalAssigned,
                'unique_outlets_visited' => $uniqueOutletsVisited,
                'coverage_percentage' => $coverageActual,
                'strike_rate' => $strikeRateActual,
                'avg_order_value' => $avgOrderValue,
            ],
            'achievement' => [
                'visit_percentage' => $visitPercent,
                'sales_percentage' => $salesPercent,
                'coverage_percentage' => $coveragePercent,
                'overall_score' => $overallScore,
                'performance_grade' => $grade,
            ],
            'leaderboard' => $leaderboard,
        ];
    }
}
