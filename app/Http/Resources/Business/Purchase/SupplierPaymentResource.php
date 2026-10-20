<?php

namespace App\Http\Resources\Business\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'purchase_id'      => $this->purchase_id,
            'purchase_number'  => $this->purchase?->purchase_number,
            'supplier_id'      => $this->supplier_id,
            'supplier_name'    => $this->supplier?->name,
            'account_id'       => $this->account_id,
            'account_name'     => $this->account?->name,
            'amount'           => (float) $this->amount,
            'payment_date'     => $this->payment_date?->format('Y-m-d'),
            'reference_number' => $this->reference_number,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}