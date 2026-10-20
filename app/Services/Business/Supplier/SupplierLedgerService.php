<?php

namespace App\Services\Business\Supplier;

use App\Models\Business\Purchase;
use App\Models\Business\Supplier;
use App\Models\Business\SupplierPayment;

class SupplierLedgerService
{
    public function getLedger(Supplier $supplier, array $filters = []): array
    {
        // Purchases
        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->when($filters['from_date'] ?? null, fn($q, $from) => $q->whereDate('purchase_date', '>=', $from))
            ->when($filters['to_date'] ?? null, fn($q, $to) => $q->whereDate('purchase_date', '<=', $to))
            ->whereNotIn('status', [PURCHASE_STATUS_CANCELLED])
            ->latest('purchase_date')
            ->get(['id', 'purchase_number', 'purchase_date', 'total_amount', 'paid_amount', 'due_amount', 'status']);

        // Payments
        $payments = SupplierPayment::where('supplier_id', $supplier->id)
            ->when($filters['from_date'] ?? null, fn($q, $from) => $q->whereDate('payment_date', '>=', $from))
            ->when($filters['to_date'] ?? null, fn($q, $to) => $q->whereDate('payment_date', '<=', $to))
            ->with(['account:id,name'])
            ->latest('payment_date')
            ->get(['id', 'purchase_id', 'account_id', 'amount', 'payment_date', 'reference_number', 'notes']);

        // Totals
        $totalPurchased  = $purchases->sum('total_amount');
        $totalPaid       = $payments->sum('amount');
        $totalOutstanding = max(0, round($totalPurchased - $totalPaid, 2));

        return [
            'supplier'         => [
                'id'    => $supplier->id,
                'name'  => $supplier->name,
                'phone' => $supplier->phone,
            ],
            'summary'          => [
                'total_purchased'   => round((float) $totalPurchased, 2),
                'total_paid'        => round((float) $totalPaid, 2),
                'total_outstanding' => $totalOutstanding,
            ],
            'purchases'        => $purchases->map(fn($p) => [
                'id'              => $p->id,
                'purchase_number' => $p->purchase_number,
                'purchase_date'   => $p->purchase_date?->format('Y-m-d'),
                'total_amount'    => (float) $p->total_amount,
                'paid_amount'     => (float) $p->paid_amount,
                'due_amount'      => (float) $p->due_amount,
                'status'          => (int) $p->status,
                'status_label'    => purchase_status_label($p->status),
            ])->values(),
            'payments'         => $payments->map(fn($pay) => [
                'id'               => $pay->id,
                'purchase_id'      => $pay->purchase_id,
                'account_name'     => $pay->account?->name,
                'amount'           => (float) $pay->amount,
                'payment_date'     => $pay->payment_date?->format('Y-m-d'),
                'reference_number' => $pay->reference_number,
                'notes'            => $pay->notes,
            ])->values(),
        ];
    }
}