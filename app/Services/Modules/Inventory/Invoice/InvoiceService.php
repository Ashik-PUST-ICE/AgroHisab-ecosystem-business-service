<?php

namespace App\Services\Modules\Inventory\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Commons\Invoice\InvoiceStatusEnum;

class InvoiceService
{
    protected Invoice $model;
    protected InvoiceItems $itemsModel;

    public function __construct()
    {
        $this->model = new Invoice;
        $this->itemsModel = new InvoiceItems;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('items');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('invoice_date', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('invoice_type', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Invoice
    {
        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discountAmount = 0;
            if (isset($data['discount_type']) && isset($data['discount_value'])) {
                if ($data['discount_type'] === 'fixed') {
                    $discountAmount = $data['discount_value'];
                } elseif ($data['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * $data['discount_value']) / 100;
                }
            }
            $amountAfterDiscount = $subtotal - $discountAmount;

            $additionalAmount = $data['additional_amount'] ?? 0;
            $amountAfterAdditional = $amountAfterDiscount + $additionalAmount;

            $vatPercentage = 0;
            $vatAmount = 0;

            if (isset($data['is_enabled_vat']) && $data['is_enabled_vat']) {
                $vatPercentage = $data['vat_percentage'] ?? 0;
                $vatAmount = ($amountAfterAdditional * $vatPercentage) / 100;
            }

            $totalAmount = $amountAfterAdditional + $vatAmount;

            $invoiceData = [
                'user_id' => $data['user_id'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'invoice_type' => $data['invoice_type'],
                'network_id' => $data['network_id'] ?? null,
                'address_id' => $data['address_id'] ?? null,
                'recurring_date' => $data['recurring_date'] ?? null,
                'created_by' => authId(),
                'subtotal' => round($subtotal, 2),
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => round($discountAmount, 2),
                'additional_amount' => $additionalAmount,
                'is_enabled_vat' => $data['is_enabled_vat'] ?? false,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($totalAmount, 2),
                'note' => $data['note'] ?? null,
                'terms_condition' => $data['terms_condition'] ?? null,
                'bank_details' => $data['bank_details'] ?? null,
                // 'paid_amount' => null,
                // 'account_id' => null,
                'status' => $data['status'] ?? InvoiceStatusEnum::UNPAID,
            ];

            $invoice = $this->model->create($invoiceData);

            foreach ($data['items'] as $item) {
                $itemData = [
                    'invoice_id' => $invoice->id,
                    'package_id' => $item['package_id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'title' => $item['title'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ];

                $this->itemsModel::create($itemData);
            }

            DB::commit();

            log_activity(
                'Created a new invoice',
                authId(),
                null,
                'invoice',
                'created',
                ['id' => $invoice->id, 'invoice_date' => $invoice->invoice_date]
            );

            return $invoice;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function show(string $id, $relations = [], $throwException = true): Invoice
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }


    public function update(string $id, array $data): Invoice
    {
        try {
            DB::beginTransaction();

            $invoice = $this->model->findOrFail($id);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discountAmount = 0;
            if (isset($data['discount_type']) && isset($data['discount_value'])) {
                if ($data['discount_type'] === 'fixed') {
                    $discountAmount = $data['discount_value'];
                } elseif ($data['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * $data['discount_value']) / 100;
                }
            }

            $amountAfterDiscount = $subtotal - $discountAmount;

            $additionalAmount = $data['additional_amount'] ?? $invoice->additional_amount;
            $amountAfterAdditional = $amountAfterDiscount + $additionalAmount;

            $vatPercentage = 0;
            $vatAmount = 0;

            if (isset($data['is_enabled_vat']) && $data['is_enabled_vat']) {
                $vatPercentage = $data['vat_percentage'] ?? 0;
                $vatAmount = ($amountAfterAdditional * $vatPercentage) / 100;
            }

            $totalAmount = $amountAfterAdditional + $vatAmount;

            $invoiceData = [
                'user_id' => $data['user_id'] ?? $invoice->user_id,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'invoice_type' => $data['invoice_type'] ?? $invoice->invoice_type,
                'network_id' => $data['network_id'] ?? $invoice->network_id,
                'address_id' => $data['address_id'] ?? $invoice->address_id,
                'recurring_date' => $data['recurring_date'] ?? $invoice->recurring_date,
                'subtotal' => round($subtotal, 2),
                'discount_type' => $data['discount_type'] ?? $invoice->discount_type,
                'discount_value' => $data['discount_value'] ?? $invoice->discount_value,
                'discount_amount' => round($discountAmount, 2),
                'additional_amount' => $additionalAmount,
                'is_enabled_vat' => $data['is_enabled_vat'] ?? $invoice->is_enabled_vat,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => round($vatAmount, 2),
                'total_amount' => round($totalAmount, 2),
                'note' => $data['note'] ?? $invoice->note,
                'terms_condition' => $data['terms_condition'] ?? $invoice->terms_condition,
                'bank_details' => $data['bank_details'] ?? $invoice->bank_details,
                'status' => $data['status'] ?? $invoice->status,
            ];

            $invoice->update($invoiceData);

            if (isset($data['items'])) {
                $invoice->items()->delete();

                foreach ($data['items'] as $item) {
                    $itemData = [
                        'invoice_id' => $invoice->id,
                        'package_id' => $item['package_id'] ?? null,
                        'product_id' => $item['product_id'] ?? null,
                        'title' => $item['title'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                    ];

                    $this->itemsModel::create($itemData);
                }
            }

            DB::commit();

            log_activity(
                'Updated a invoice',
                authId(),
                null,
                'invoice',
                'updated',
                $invoice->getChanges() + ['id' => $invoice->id]
            );

            return $invoice->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }



    public function destroy(string $id): void
    {
        $invoice = $this->show($id);
        $invoice->delete();
        log_activity('Deleted an invoice', authId(), null, 'invoice', 'deleted', $invoice->toArray());
    }

    public function toggleStatus(string $id): Invoice
    {
        $invoice = $this->show($id);

        $invoice->status = match ($invoice->status) {
            InvoiceStatusEnum::PAID => InvoiceStatusEnum::UNPAID,
            InvoiceStatusEnum::UNPAID => InvoiceStatusEnum::PARTIALLY_PAID,
            InvoiceStatusEnum::PARTIALLY_PAID => InvoiceStatusEnum::PAID,
        };
        $invoice->save();

        log_activity('Toggled invoice status to ' . ($invoice->status->value), authId(), null, 'invoice', 'updated', $invoice->getChanges() + ['id' => $invoice->id]);
        return $invoice;
    }

    public function list(array $filters = []): array
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('invoice_date', 'like', '%' . $filters['search'] . '%');
        });

        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $invoices = $query->select('id', 'invoice_date', 'status')->orderBy('invoice_date')->get();

        return $invoices->map(function ($invoice) {
            return [
                'value' => $invoice->id,
                'label' => 'Invoice #' . $invoice->id . ' - ' . $invoice->invoice_date,
            ];
        })->toArray();
    }

    public function invoiceDetails(string $id): array
    {
        try {
            $invoice = $this->model->with([
                'payments.account',
                'payments.transaction',
            ])->findOrFail($id);


            $invoiceInfo = [
                // 'sl' => $invoice->id,
                'date' => $invoice->invoice_date->format('d-m-Y'),
                'invoice' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                'total_amount' => (float) $invoice->total_amount,
                'status' => $invoice->status?->label(),
            ];

            $totalPaid = $invoice->payments()->sum('payment_amount');


            $paymentSummary = [
                'total_amount' => (float) $invoice->total_amount,
                'total_paid' => (float) $totalPaid,
                'status' => $invoice->status?->label(),
            ];


            $payments = [];
            // $paymentCount = 1;

            foreach ($invoice->payments as $payment) {
                $payments[] = [
                    // 'sl' => $paymentCount++,
                    'date' => $payment->created_at->format('d-m-Y'),
                    'invoice_note' => $payment->note ?? 'N/A',
                    'payment_account_name' => $payment->account->account_holder_name,
                    'account_id' => $payment->account->id,
                    'title' => $payment->account->title,
                    'paid_amount' => (float) $payment->payment_amount,
                    'transaction_id' => $payment->transaction->transaction_id,
                ];
            }

            return [
                'invoice' => $invoiceInfo,
                'payment_summary' => $paymentSummary,
                'payment_information' => $payments,
            ];
        } catch (\Throwable $e) {
            log_activity('Get invoice details failed', authId(), null, 'invoice', 'failed', ['error' => $e->getMessage(), 'invoice_id' => $id], 'error');
            throw $e;
        }
    }



    public function getRecurringInvoices(): array
    {
        try {
            $recurringInvoices = $this->model
                ->whereNotNull('recurring_date')
                ->with([
                    'items.product',
                    'payments'
                ])
                ->orderBy('recurring_date', 'asc')
                ->get();

            $invoices = [];

            foreach ($recurringInvoices as $invoice) {
                $totalPaid = $invoice->payments()->sum('payment_amount');
                $remainingAmount = $invoice->total_amount - $totalPaid;

                $invoices[] = [

                    'date' => $invoice->invoice_date->format('d-m-Y'),
                    'invoice' => 'INV-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                    'total_amount' => (float) $invoice->total_amount,
                    'paid_amount' => (float) $totalPaid,
                    'remaining_amount' => (float) $remainingAmount,
                    'recurring_date' => $invoice->recurring_date->format('d-m-Y'),
                    'status' => $invoice->status?->label(),
                ];
            }

            return [
                'recurring_invoices' => $invoices,

            ];
        } catch (\Throwable $e) {
            log_activity('Get recurring invoices failed', authId(), null, 'invoice', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }
}
