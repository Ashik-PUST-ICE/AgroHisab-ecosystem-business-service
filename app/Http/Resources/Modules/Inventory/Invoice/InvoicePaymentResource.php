<?php

namespace App\Http\Resources\Modules\Inventory\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoicePaymentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'invoice_id' => $this->invoice_id,
            'account_id' => $this->account_id,
            'transaction_id' => $this->transaction_id,
            'payment_amount' => (float) $this->payment_amount,
            'note' => $this->note,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),


            'invoice' => $this->whenLoaded('invoice', function () {
                return [
                    'id' => $this->invoice->id,
                    'invoice_date' => $this->invoice->invoice_date,
                    'invoice_type' => $this->invoice->invoice_type,
                    'total_amount' => (float) $this->invoice->total_amount,
                    'formatted_status' => $this->invoice->status?->label(),
                ];
            }),

            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'title' => $this->account->title,
                    'account_holder_name' => $this->account->account_holder_name,
                    'account_no' => $this->account->account_no,
                    'routing_no' => $this->account->routing_no,
                ];
            }),

            'transaction' => $this->whenLoaded('transaction', function () {
                return [
                    'id' => $this->transaction->id,
                    'type' => $this->transaction->type,
                    'amount' => (float) $this->transaction->amount,
                    'transaction_id' => $this->transaction->transaction_id,
                    'description' => $this->transaction->description,
                    'transaction_date' => $this->transaction->transaction_date?->format('Y-m-d'),
                ];
            }),
        ];
    }
}
