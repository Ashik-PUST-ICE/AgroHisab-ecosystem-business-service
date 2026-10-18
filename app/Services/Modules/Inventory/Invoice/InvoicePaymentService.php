<?php

namespace App\Services\Modules\Inventory\Invoice;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Commons\Invoice\InvoiceStatusEnum;

class InvoicePaymentService
{
    protected InvoicePayment $model;

    public function __construct()
    {
        $this->model = new InvoicePayment;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['invoice', 'account', 'transaction']);

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('note', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('invoice', function ($invoiceQuery) use ($filters) {
                        $invoiceQuery->where('invoice_date', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        if (isset($filters['invoice_id'])) {
            $query->where('invoice_id', $filters['invoice_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): InvoicePayment
    {
        try {
            DB::beginTransaction();

            $paymentAmount = $data['payment_amount'] ?? 0;

            $invoice = null;
            if (isset($data['invoice_id']) && $data['invoice_id']) {
                $invoice = Invoice::findOrFail($data['invoice_id']);

                $totalPaid = $invoice->payments()->sum('payment_amount');
                $remainingAmount = $invoice->total_amount - $totalPaid;

                if ($paymentAmount > $remainingAmount) {
                    throw new \Exception("Payment amount ({$paymentAmount}) exceeds remaining amount ({$remainingAmount}). Please enter a valid amount.");
                }
            }

            $invoicePaymentData = [
                'user_id' => $data['user_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'account_id' => $data['account_id'] ?? null,
                'payment_amount' => $paymentAmount,
                'note' => $data['note'] ?? null,
                'created_by' => authId(),
            ];

            $invoicePayment = $this->model->create($invoicePaymentData);

            if (isset($data['account_id']) && $data['account_id']) {
                $transaction = Transaction::create([
                    'type' => 'credit',
                    'account_id' => $data['account_id'],
                    'description' => 'Invoice Payment - ' . ($invoice ? 'Invoice #' . $invoice->id : 'Payment #' . $invoicePayment->id),
                    'amount' => $paymentAmount,
                    'transaction_date' => now(),
                    'created_by' => authId(),
                    'transactionable_type' => InvoicePayment::class,
                    'transactionable_id' => $invoicePayment->id,
                ]);

                $invoicePayment->update(['transaction_id' => $transaction->id]);
            }

            if ($invoice) {
                $totalPaid = $invoice->payments()->sum('payment_amount');

                if ($totalPaid >= $invoice->total_amount) {
                    $invoice->update(['status' => InvoiceStatusEnum::PAID]);
                } elseif ($totalPaid > 0 && $totalPaid < $invoice->total_amount) {
                    $invoice->update(['status' => InvoiceStatusEnum::PARTIALLY_PAID]);
                } else {
                    $invoice->update(['status' => InvoiceStatusEnum::UNPAID]);
                }
            }

            DB::commit();

            log_activity(
                'Created a new invoice payment',
                authId(),
                null,
                'invoice_payment',
                'created',
                ['id' => $invoicePayment->id, 'amount' => $paymentAmount, 'invoice_id' => $invoice?->id]
            );

            return $invoicePayment->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function show(string $id, $relations = [], $throwException = true): InvoicePayment
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }
        return $query->find($id);
    }



}
