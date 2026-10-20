<?php

namespace App\Http\Resources\Business\Expense;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'expense_number'      => $this->expense_number,
            'expense_category_id' => $this->expense_category_id,
            'category_name'       => $this->category?->name,
            'purchase_id'         => $this->purchase_id,
            'purchase_number'     => $this->purchase?->purchase_number,
            'shipment_id'         => $this->shipment_id,
            'shipment_number'     => $this->shipment?->shipment_number,
            'account_id'          => $this->account_id,
            'account_name'        => $this->account?->name,
            'title'               => $this->title,
            'amount'              => (float) $this->amount,
            'expense_date'        => $this->expense_date?->format('Y-m-d'),
            'paid_by'             => $this->paid_by,
            'payment_status'      => $this->payment_status,
            'payment_status_label'=> expense_payment_status_label($this->payment_status),
            'notes'               => $this->notes,
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}