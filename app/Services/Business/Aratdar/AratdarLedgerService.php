<?php

namespace App\Services\Business\Aratdar;

use App\Models\Business\Aratdar;
use App\Models\Business\AratdarRemittance;
use App\Models\Business\ShipmentSale;
use Carbon\Carbon;

class AratdarLedgerService
{
    public function ledger(Aratdar $aratdar, array $filters = []): array
    {
        $fromDate = !empty($filters['from_date']) ? Carbon::parse($filters['from_date'])->toDateString() : null;
        $toDate   = !empty($filters['to_date'])   ? Carbon::parse($filters['to_date'])->toDateString()   : null;

        // Sales & commission recorded against shipments of this aratdar
        $salesQuery = ShipmentSale::whereHas('shipment', fn($q) => $q->where('aratdar_id', $aratdar->id))
            ->with(['shipment:id,shipment_number', 'product:id,name']);
        if ($fromDate) $salesQuery->whereDate('sale_date', '>=', $fromDate);
        if ($toDate)   $salesQuery->whereDate('sale_date', '<=', $toDate);
        $sales = $salesQuery->orderBy('sale_date')->get();

        // Remittances received from this aratdar
        $remitQuery = AratdarRemittance::where('aratdar_id', $aratdar->id);
        if ($fromDate) $remitQuery->whereDate('payment_date', '>=', $fromDate);
        if ($toDate)   $remitQuery->whereDate('payment_date', '<=', $toDate);
        $remittances = $remitQuery->orderBy('payment_date')->get();

        // Aggregated totals
        $totalGrossSales  = $sales->sum('gross_amount');
        $totalCommission  = $sales->sum('commission_amount');
        $totalNetProceeds = $sales->sum('net_proceeds'); // amount aratdar owes owner
        $totalRemitted    = $remittances->sum('amount');
        $outstanding      = round((float)$totalNetProceeds - (float)$totalRemitted, 2);

        // Build chronological ledger entries
        $entries = [];

        foreach ($sales as $s) {
            $entries[] = [
                'date'            => $s->sale_date?->toDateString(),
                'type'            => 'sale',
                'description'     => 'Sale — ' . ($s->product?->name ?? 'Product') . ' [' . ($s->shipment?->shipment_number ?? '') . ']',
                'debit'           => (float) $s->net_proceeds,  // aratdar owes this amount
                'credit'          => 0.00,
                'commission'      => (float) $s->commission_amount,
                'reference_id'    => $s->id,
                'reference_type'  => 'shipment_sale',
            ];
        }

        foreach ($remittances as $r) {
            $entries[] = [
                'date'            => $r->payment_date?->toDateString(),
                'type'            => 'remittance',
                'description'     => 'Remittance — ' . ($r->payment_reference ?? 'N/A'),
                'debit'           => 0.00,
                'credit'          => (float) $r->amount,       // payment received
                'commission'      => 0.00,
                'reference_id'    => $r->id,
                'reference_type'  => 'aratdar_remittance',
            ];
        }

        // Sort chronologically
        usort($entries, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));

        // Add running balance column
        $running = 0.0;
        foreach ($entries as &$e) {
            $running += $e['debit'] - $e['credit'];
            $e['running_balance'] = round($running, 2);
        }
        unset($e);

        return [
            'aratdar' => [
                'id'              => $aratdar->id,
                'name'            => $aratdar->name,
                'city'            => $aratdar->city ?? null,
                'commission_type' => $aratdar->commission_type,
                'commission_value'=> (float) ($aratdar->commission_value ?? 0),
            ],
            'period'  => ['from' => $fromDate, 'to' => $toDate],
            'summary' => [
                'total_gross_sales'   => (float) $totalGrossSales,
                'total_commission'    => (float) $totalCommission,
                'total_net_proceeds'  => (float) $totalNetProceeds,
                'total_remitted'      => (float) $totalRemitted,
                'outstanding_balance' => $outstanding,
            ],
            'entries' => $entries,
        ];
    }
}