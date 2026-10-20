<?php

namespace App\Services\Business\Purchase;

use App\Models\Business\Purchase;
use App\Models\Business\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierPaymentService
{
    public function store(Purchase $purchase, array $data): SupplierPayment
    {
        $amount = (float) $data['amount'];

        if ($amount > (float) $purchase->due_amount) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Payment amount (' . $amount . ') exceeds outstanding due (' . $purchase->due_amount . ').',
                ],
            ]);
        }

        return DB::transaction(function () use ($purchase, $data, $amount) {
            $payment = SupplierPayment::create([
                ...$data,
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'created_by'  => authId(),
            ]);

            $newPaid = round((float) $purchase->paid_amount + $amount, 2);
            $newDue  = max(0, round((float) $purchase->total_amount - $newPaid, 2));

            $purchase->update([
                'paid_amount' => $newPaid,
                'due_amount'  => $newDue,
            ]);

            return $payment->load(['purchase', 'supplier', 'account']);
        });
    }

    public function index(Purchase $purchase): \Illuminate\Database\Eloquent\Collection
    {
        return $purchase->payments()->with(['account'])->latest()->get();
    }
}