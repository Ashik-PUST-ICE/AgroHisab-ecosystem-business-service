<?php

namespace App\Services\Business\Report;

use App\Models\Business\Account;
use App\Models\Business\AratdarRemittance;
use App\Models\Business\Product;
use App\Models\Business\PurchaseItem;
use App\Models\Business\Shipment;
use App\Models\Business\StockMovement;
use App\Models\Business\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public function getCashbookReport(array $filters = []): array
    {
        $accountId = $filters['account_id'] ?? null;
        $fromDate  = !empty($filters['from_date']) ? Carbon::parse($filters['from_date'])->toDateString() : null;
        $toDate    = !empty($filters['to_date']) ? Carbon::parse($filters['to_date'])->toDateString() : null;

        $accountsQuery = Account::where('status', STATUS_ACTIVE);
        if ($accountId) {
            $accountsQuery->where('id', $accountId);
        }
        $accounts = $accountsQuery->get();
        $accountIds = $accounts->pluck('id')->toArray();

        // 1. Calculate opening balance
        $baseOpeningBalance = (float) $accounts->sum('opening_balance');

        $priorInflows = 0;
        $priorOutflows = 0;
        if ($fromDate) {
            $priorInflows = (float) AratdarRemittance::whereIn('account_id', $accountIds)
                ->whereDate('payment_date', '<', $fromDate)
                ->sum('amount');

            $priorOutflows = (float) SupplierPayment::whereIn('account_id', $accountIds)
                ->whereDate('payment_date', '<', $fromDate)
                ->sum('amount');
        }

        $openingBalance = round($baseOpeningBalance + $priorInflows - $priorOutflows, 2);

        // 2. Fetch period transactions
        $remittancesQuery = AratdarRemittance::with(['aratdar', 'account'])->whereIn('account_id', $accountIds);
        $paymentsQuery    = SupplierPayment::with(['supplier', 'account'])->whereIn('account_id', $accountIds);

        if ($fromDate) {
            $remittancesQuery->whereDate('payment_date', '>=', $fromDate);
            $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $remittancesQuery->whereDate('payment_date', '<=', $toDate);
            $paymentsQuery->whereDate('payment_date', '<=', $toDate);
        }

        $remittances = $remittancesQuery->get();
        $payments    = $paymentsQuery->get();

        $transactions = new Collection();

        foreach ($remittances as $rem) {
            $transactions->push([
                'id'             => 'REM-' . $rem->id,
                'date'           => Carbon::parse($rem->payment_date)->toDateString(),
                'type'           => 'credit', // Inflow
                'type_label'     => 'আড়তদার রেমিট্যান্স (জমা)',
                'reference'      => $rem->remittance_number,
                'party_name'     => $rem->aratdar?->name,
                'account_name'   => $rem->account?->name,
                'credit_amount'  => (float) $rem->amount,
                'debit_amount'   => 0.0,
                'notes'          => $rem->notes,
                'timestamp'      => Carbon::parse($rem->payment_date)->timestamp,
            ]);
        }

        foreach ($payments as $pay) {
            $transactions->push([
                'id'             => 'PAY-' . $pay->id,
                'date'           => Carbon::parse($pay->payment_date)->toDateString(),
                'type'           => 'debit', // Outflow
                'type_label'     => 'সরবরাহকারী পরিশোধ (খরচ)',
                'reference'      => $pay->reference_number ?: ('SP-' . $pay->id),
                'party_name'     => $pay->supplier?->name,
                'account_name'   => $pay->account?->name,
                'credit_amount'  => 0.0,
                'debit_amount'   => (float) $pay->amount,
                'notes'          => $pay->notes,
                'timestamp'      => Carbon::parse($pay->payment_date)->timestamp,
            ]);
        }

        $sorted = $transactions->sortBy('timestamp')->values();

        // 3. Compute running balance
        $currentRunning = $openingBalance;
        $totalInflows = 0;
        $totalOutflows = 0;

        $ledgerRows = [];
        foreach ($sorted as $tx) {
            $credit = (float) $tx['credit_amount'];
            $debit  = (float) $tx['debit_amount'];

            $currentRunning = round($currentRunning + $credit - $debit, 2);
            $totalInflows   += $credit;
            $totalOutflows  += $debit;

            $tx['running_balance'] = $currentRunning;
            $ledgerRows[] = $tx;
        }

        $closingBalance = $currentRunning;

        return [
            'filters' => [
                'account_id' => $accountId,
                'from_date'  => $fromDate,
                'to_date'    => $toDate,
            ],
            'summary' => [
                'opening_balance' => $openingBalance,
                'total_inflows'   => round($totalInflows, 2),
                'total_outflows'  => round($totalOutflows, 2),
                'net_cashflow'    => round($totalInflows - $totalOutflows, 2),
                'closing_balance' => $closingBalance,
            ],
            'transactions' => $ledgerRows,
        ];
    }

    public function getInventoryValuationReport(array $filters = []): array
    {
        $productsQuery = Product::with(['category', 'unit'])->where('status', STATUS_ACTIVE);

        if (!empty($filters['product_id'])) {
            $productsQuery->where('id', $filters['product_id']);
        }

        if (!empty($filters['category_id'])) {
            $productsQuery->where('category_id', $filters['category_id']);
        }

        $products = $productsQuery->get();

        $rows = [];
        $totalStockQty = 0;
        $totalAssetValue = 0;

        foreach ($products as $prod) {
            // Aggregate all stock movements
            $movements = StockMovement::where('product_id', $prod->id);

            if (!empty($filters['as_of_date'])) {
                $asOf = Carbon::parse($filters['as_of_date'])->toDateString();
                $movements->whereDate('created_at', '<=', $asOf);
            }

            $currentStock = (float) $movements->sum('quantity');

            // Weighted or latest average purchase unit rate
            $latestPurchaseItem = PurchaseItem::where('product_id', $prod->id)->latest('id')->first();
            $avgUnitRate = $latestPurchaseItem ? (float) $latestPurchaseItem->unit_rate : 0.0;

            $valuation = max(0, round($currentStock * $avgUnitRate, 2));

            $totalStockQty += max(0, $currentStock);
            $totalAssetValue += $valuation;

            $rows[] = [
                'product_id'        => $prod->id,
                'product_name'      => $prod->name,
                'category_name'     => $prod->category?->name,
                'unit_name'         => $prod->unit?->name,
                'current_stock'     => round($currentStock, 4),
                'unit_cost_rate'    => $avgUnitRate,
                'asset_valuation'   => $valuation,
                'status'            => $currentStock > 0 ? 'in_stock' : 'out_of_stock',
            ];
        }

        return [
            'filters' => [
                'as_of_date'  => !empty($filters['as_of_date']) ? Carbon::parse($filters['as_of_date'])->toDateString() : Carbon::now()->toDateString(),
                'category_id' => $filters['category_id'] ?? null,
                'product_id'  => $filters['product_id'] ?? null,
            ],
            'summary' => [
                'total_products_tracked' => count($rows),
                'total_inventory_weight' => round($totalStockQty, 4),
                'total_asset_valuation'  => round($totalAssetValue, 2),
            ],
            'data' => $rows,
        ];
    }
}