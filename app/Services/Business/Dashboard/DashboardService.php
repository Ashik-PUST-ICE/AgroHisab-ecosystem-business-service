<?php

namespace App\Services\Business\Dashboard;

use App\Models\Business\Account;
use App\Models\Business\AratdarRemittance;
use App\Models\Business\Purchase;
use App\Models\Business\Settlement;
use App\Models\Business\Shipment;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getMetrics(): array
    {
        // 1. Accounts Liquid Balance
        $accounts = Account::where('status', STATUS_ACTIVE)->get();
        $totalAccountBalance = (float) $accounts->sum('opening_balance');
        $accountsBreakdown = $accounts->map(fn($acc) => [
            'id'              => $acc->id,
            'name'            => $acc->name,
            'type'            => $acc->type,
            'type_label'      => account_type_label($acc->type),
            'current_balance' => (float) $acc->opening_balance,
        ]);

        // 2. Supplier Payables Due
        $totalSupplierDue = (float) Purchase::whereIn('status', [PURCHASE_STATUS_CONFIRMED, PURCHASE_STATUS_RECEIVED])
            ->sum('due_amount');

        // 3. Aratdar Open Receivables & Unallocated Remittances
        $unallocatedRemittances = (float) AratdarRemittance::sum('unallocated_amount');

        // Receivables from active unsettled shipments
        $activeShipments = Shipment::whereIn('status', [
            SHIPMENT_STATUS_DISPATCHED,
            SHIPMENT_STATUS_RECEIVED,
            SHIPMENT_STATUS_SELLING,
        ])->with(['sales', 'allocations'])->get();

        $openReceivables = 0;
        foreach ($activeShipments as $shp) {
            $netSalesProceeds = (float) $shp->sales->sum('net_proceeds');
            $remitted = (float) $shp->allocations->sum('amount');
            $openReceivables += max(0, $netSalesProceeds - $remitted);
        }

        // 4. Shipments Count Breakdown
        $shipmentsCount = [
            'draft'       => Shipment::where('status', SHIPMENT_STATUS_DRAFT)->count(),
            'dispatched'  => Shipment::where('status', SHIPMENT_STATUS_DISPATCHED)->count(),
            'received'    => Shipment::where('status', SHIPMENT_STATUS_RECEIVED)->count(),
            'selling'     => Shipment::where('status', SHIPMENT_STATUS_SELLING)->count(),
            'settled'     => Shipment::where('status', SHIPMENT_STATUS_SETTLED)->count(),
            'total'       => Shipment::count(),
        ];

        // 5. Profitability & Settlement Summary
        $settledCount = Settlement::count();
        $totalGrossSales = (float) Settlement::sum('gross_sales');
        $totalRealizedProfit = (float) Settlement::sum('realized_profit');
        $totalCommissionDeducted = (float) Settlement::sum('commission_amount');

        return [
            'liquidity' => [
                'total_liquid_balance' => round($totalAccountBalance, 2),
                'accounts'             => $accountsBreakdown,
            ],
            'receivables' => [
                'open_aratdar_receivables' => round($openReceivables, 2),
                'unallocated_remittances'  => round($unallocatedRemittances, 2),
                'net_receivable_due'       => max(0, round($openReceivables - $unallocatedRemittances, 2)),
            ],
            'payables' => [
                'total_supplier_due' => round($totalSupplierDue, 2),
            ],
            'consignments' => [
                'breakdown' => $shipmentsCount,
            ],
            'financials' => [
                'settled_shipments_count' => $settledCount,
                'total_gross_sales'       => round($totalGrossSales, 2),
                'total_commission_paid'   => round($totalCommissionDeducted, 2),
                'total_realized_profit'   => round($totalRealizedProfit, 2),
            ],
        ];
    }
}