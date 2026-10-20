<?php

namespace App\Services\Business\Report;

use App\Models\Business\Aratdar;
use App\Models\Business\AratdarRemittance;
use App\Models\Business\Purchase;
use App\Models\Business\Shipment;
use App\Models\Business\ShipmentSale;
use App\Models\Business\SupplierPayment;
use Carbon\Carbon;

class ExtendedReportService
{
    /**
     * Receivables Aging: outstanding amounts owed by each aratdar.
     * Spec §3.14: "Receivables/payables aging"
     * Buckets: 0-30 days, 31-60, 61-90, 90+
     */
    public function receivablesAging(array $filters = []): array
    {
        $today = Carbon::today();

        $aratdarsQuery = Aratdar::query();
        if (!empty($filters['aratdar_id'])) {
            $aratdarsQuery->where('id', $filters['aratdar_id']);
        }

        $rows = [];
        $totals = ['0_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0, 'total' => 0.0];

        foreach ($aratdarsQuery->get() as $aratdar) {
            // Net proceeds owed per settled/open shipment
            $shipments = Shipment::where('aratdar_id', $aratdar->id)
                ->whereIn('status', [SHIPMENT_STATUS_RECEIVED, SHIPMENT_STATUS_SELLING, SHIPMENT_STATUS_RECONCILIATION_PENDING])
                ->with(['sales', 'allocations'])
                ->get();

            $row = ['0_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0, 'total' => 0.0];

            foreach ($shipments as $shipment) {
                $netProceeds   = (float) $shipment->sales->sum('net_proceeds');
                $paid          = (float) $shipment->allocations->sum('allocated_amount');
                $outstanding   = round($netProceeds - $paid, 2);

                if ($outstanding <= 0) continue;

                $daysOld = (int) Carbon::parse($shipment->dispatch_date ?? $shipment->created_at)->diffInDays($today);

                if ($daysOld <= 30)      $bucket = '0_30';
                elseif ($daysOld <= 60)  $bucket = '31_60';
                elseif ($daysOld <= 90)  $bucket = '61_90';
                else                     $bucket = '90_plus';

                $row[$bucket] += $outstanding;
                $row['total'] += $outstanding;
            }

            if ($row['total'] > 0) {
                $rows[] = [
                    'aratdar_id'   => $aratdar->id,
                    'aratdar_name' => $aratdar->name,
                    'city'         => $aratdar->city ?? null,
                    '0_30_days'    => round($row['0_30'], 2),
                    '31_60_days'   => round($row['31_60'], 2),
                    '61_90_days'   => round($row['61_90'], 2),
                    '90_plus_days' => round($row['90_plus'], 2),
                    'total_due'    => round($row['total'], 2),
                ];
                foreach ($totals as $k => $_) {
                    $key = $k === 'total' ? 'total' : $k;
                    $totals[$k] += $row[$key === 'total' ? 'total' : $key] ?? 0;
                }
            }
        }

        usort($rows, fn($a, $b) => $b['total_due'] <=> $a['total_due']);

        return [
            'report_date' => $today->toDateString(),
            'summary'     => [
                '0_30_days'    => round($totals['0_30'], 2),
                '31_60_days'   => round($totals['31_60'], 2),
                '61_90_days'   => round($totals['61_90'], 2),
                '90_plus_days' => round($totals['90_plus'], 2),
                'grand_total'  => round($totals['total'], 2),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Payables Aging: outstanding amounts owed to each supplier.
     * Spec §3.14: "Receivables/payables aging"
     */
    public function payablesAging(array $filters = []): array
    {
        $today = Carbon::today();

        $query = Purchase::with(['supplier', 'payments'])
            ->where('due_amount', '>', 0);

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        $purchases = $query->get();

        // Group by supplier
        $supplierMap = [];
        foreach ($purchases as $p) {
            $sid = $p->supplier_id;
            if (!isset($supplierMap[$sid])) {
                $supplierMap[$sid] = [
                    'supplier_id'   => $sid,
                    'supplier_name' => $p->supplier?->name ?? 'Unknown',
                    '0_30'  => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0,
                    'total' => 0.0,
                ];
            }

            $due     = (float) $p->due_amount;
            $daysOld = (int) Carbon::parse($p->purchase_date)->diffInDays($today);

            if ($daysOld <= 30)      $bucket = '0_30';
            elseif ($daysOld <= 60)  $bucket = '31_60';
            elseif ($daysOld <= 90)  $bucket = '61_90';
            else                     $bucket = '90_plus';

            $supplierMap[$sid][$bucket] += $due;
            $supplierMap[$sid]['total'] += $due;
        }

        $rows = [];
        $totals = ['0_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0, 'grand_total' => 0.0];

        foreach ($supplierMap as $row) {
            $rows[] = [
                'supplier_id'   => $row['supplier_id'],
                'supplier_name' => $row['supplier_name'],
                '0_30_days'     => round($row['0_30'], 2),
                '31_60_days'    => round($row['31_60'], 2),
                '61_90_days'    => round($row['61_90'], 2),
                '90_plus_days'  => round($row['90_plus'], 2),
                'total_due'     => round($row['total'], 2),
            ];
            $totals['0_30']       += $row['0_30'];
            $totals['31_60']      += $row['31_60'];
            $totals['61_90']      += $row['61_90'];
            $totals['90_plus']    += $row['90_plus'];
            $totals['grand_total']+= $row['total'];
        }

        usort($rows, fn($a, $b) => $b['total_due'] <=> $a['total_due']);

        return [
            'report_date' => $today->toDateString(),
            'summary'     => [
                '0_30_days'    => round($totals['0_30'], 2),
                '31_60_days'   => round($totals['31_60'], 2),
                '61_90_days'   => round($totals['61_90'], 2),
                '90_plus_days' => round($totals['90_plus'], 2),
                'grand_total'  => round($totals['grand_total'], 2),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Sales by Product/Market report.
     * Spec §3.14: "Sales and purchases by date, product, market, supplier, and aratdar"
     */
    public function salesByProduct(array $filters = []): array
    {
        $query = ShipmentSale::query()
            ->with(['product:id,name', 'shipment:id,shipment_number,aratdar_id,market_id',
                    'shipment.market:id,name', 'shipment.aratdar:id,name']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('sale_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('sale_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }
        if (!empty($filters['aratdar_id'])) {
            $query->whereHas('shipment', fn($q) => $q->where('aratdar_id', $filters['aratdar_id']));
        }

        $sales = $query->get();

        // Group by product
        $productMap = [];
        foreach ($sales as $s) {
            $pid = $s->product_id;
            if (!isset($productMap[$pid])) {
                $productMap[$pid] = [
                    'product_id'       => $pid,
                    'product_name'     => $s->product?->name,
                    'total_quantity'   => 0.0,
                    'total_gross'      => 0.0,
                    'total_commission' => 0.0,
                    'total_net'        => 0.0,
                    'sale_count'       => 0,
                ];
            }
            $productMap[$pid]['total_quantity']   += (float) $s->quantity;
            $productMap[$pid]['total_gross']      += (float) $s->gross_amount;
            $productMap[$pid]['total_commission'] += (float) $s->commission_amount;
            $productMap[$pid]['total_net']        += (float) $s->net_proceeds;
            $productMap[$pid]['sale_count']++;
        }

        $rows = array_values(array_map(function ($r) {
            return [
                'product_id'       => $r['product_id'],
                'product_name'     => $r['product_name'],
                'sale_count'       => $r['sale_count'],
                'total_quantity'   => round($r['total_quantity'], 4),
                'total_gross'      => round($r['total_gross'], 2),
                'total_commission' => round($r['total_commission'], 2),
                'total_net'        => round($r['total_net'], 2),
            ];
        }, $productMap));

        usort($rows, fn($a, $b) => $b['total_gross'] <=> $a['total_gross']);

        return [
            'period' => [
                'from' => $filters['from_date'] ?? null,
                'to'   => $filters['to_date'] ?? null,
            ],
            'summary' => [
                'total_sales'      => $sales->count(),
                'total_gross'      => round($sales->sum('gross_amount'), 2),
                'total_commission' => round($sales->sum('commission_amount'), 2),
                'total_net'        => round($sales->sum('net_proceeds'), 2),
            ],
            'rows' => $rows,
        ];
    }
}