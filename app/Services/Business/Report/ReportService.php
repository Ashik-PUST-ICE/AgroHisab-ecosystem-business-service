<?php

namespace App\Services\Business\Report;

use App\Models\Business\Shipment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function getProfitabilityReport(array $filters = []): array
    {
        $query = Shipment::with([
            'aratdar',
            'market',
            'transporter',
            'items.product',
            'sales',
            'allocations',
            'settlement',
        ]);

        if (!empty($filters['aratdar_id'])) {
            $query->where('aratdar_id', $filters['aratdar_id']);
        }

        if (!empty($filters['market_id'])) {
            $query->where('market_id', $filters['market_id']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('dispatch_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('dispatch_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        $shipments = $query->latest('dispatch_date')->get();

        $rows = [];
        $totalGross = 0;
        $totalCommission = 0;
        $totalFreight = 0;
        $totalCogs = 0;
        $totalRealizedProfit = 0;

        foreach ($shipments as $shp) {
            $dispatchedWeight = (float) $shp->items->sum('dispatched_weight');
            $receivedWeight   = (float) $shp->items->sum('received_weight');
            $shortageWeight   = (float) $shp->items->sum('shortage_weight');

            $grossSales = (float) $shp->sales->sum('gross_amount');
            $commission = (float) $shp->sales->sum('commission_amount');
            $netSalesProceeds = (float) $shp->sales->sum('net_proceeds');
            $freightAmount = (float) $shp->freight_amount;

            // COGS of sold
            $cogs = 0;
            foreach ($shp->items as $item) {
                $soldWeight = (float) $shp->sales->filter(fn($s) => $s->shipment_item_id === $item->id || (!$s->shipment_item_id && $s->product_id === $item->product_id))->sum('quantity');
                $cogs += round($soldWeight * (float) $item->unit_rate, 2);
            }

            $ownerFreight = ($shp->freight_paid_by === 'owner') ? $freightAmount : 0;
            $realizedProfit = ($shp->settlement)
                ? (float) $shp->settlement->realized_profit
                : round($netSalesProceeds - $cogs - $ownerFreight, 2);

            $margin = $grossSales > 0 ? round(($realizedProfit / $grossSales) * 100, 2) : 0;

            $totalGross += $grossSales;
            $totalCommission += $commission;
            $totalFreight += $ownerFreight;
            $totalCogs += $cogs;
            $totalRealizedProfit += $realizedProfit;

            $rows[] = [
                'shipment_id'            => $shp->id,
                'shipment_number'        => $shp->shipment_number,
                'dispatch_date'          => $shp->dispatch_date ? Carbon::parse($shp->dispatch_date)->toDateString() : null,
                'status'                 => $shp->status,
                'status_label'           => shipment_status_label($shp->status),
                'aratdar_name'           => $shp->aratdar?->name,
                'market_name'            => $shp->market?->name,
                'dispatched_weight'      => $dispatchedWeight,
                'received_weight'        => $receivedWeight,
                'shortage_weight'        => $shortageWeight,
                'gross_sales'            => $grossSales,
                'commission'             => $commission,
                'owner_freight'          => $ownerFreight,
                'allocated_cogs'         => $cogs,
                'realized_profit'        => $realizedProfit,
                'profit_margin_percent'  => $margin,
                'is_settled'             => $shp->status === SHIPMENT_STATUS_SETTLED,
            ];
        }

        $avgMargin = $totalGross > 0 ? round(($totalRealizedProfit / $totalGross) * 100, 2) : 0;

        return [
            'filters' => [
                'from_date'    => !empty($filters['from_date']) ? Carbon::parse($filters['from_date'])->toDateString() : null,
                'to_date'      => !empty($filters['to_date']) ? Carbon::parse($filters['to_date'])->toDateString() : null,
                'aratdar_id'   => $filters['aratdar_id'] ?? null,
                'market_id'    => $filters['market_id'] ?? null,
            ],
            'summary' => [
                'shipment_count'         => count($rows),
                'total_gross_sales'      => round($totalGross, 2),
                'total_commission'       => round($totalCommission, 2),
                'total_freight_expense'  => round($totalFreight, 2),
                'total_cogs'             => round($totalCogs, 2),
                'total_realized_profit'  => round($totalRealizedProfit, 2),
                'average_profit_margin'  => $avgMargin,
            ],
            'data' => $rows,
        ];
    }
}