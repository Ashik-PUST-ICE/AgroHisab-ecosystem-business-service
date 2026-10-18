<?php

namespace App\Http\Resources\Modules\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'bill_no' => $this->bill_no,
            'expense_date' => $this->expense_date?->format('Y-m-d'),
            'account' => $this->whenLoaded('account'),
            'account_id' => $this->account_id,
            'amount' => $this->amount,
            'voucher' => $this->voucher,
            'payment_status' => $this->payment_status?->label(),
            'payment_status_value' => $this->payment_status?->value,
            'payment_status_badge' => $this->getPaymentStatusBadge(),
            'description' => $this->description,
            'finance_category' => $this->whenLoaded('financeCategory'),
            'finance_category_id' => $this->finance_category_id,
            'created_by' => $this->created_by,
            'transactions' => $this->whenLoaded('transactions'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function getPaymentStatusBadge(): string
    {
        return match ($this->payment_status?->value) {
            1 => 'paid',
            0 => 'unpaid',
            2 => 'due',
        };
    }
}
