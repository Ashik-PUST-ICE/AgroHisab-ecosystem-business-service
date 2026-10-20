<?php

namespace App\Http\Resources\Business\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AratdarRemittanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'remittance_number'  => $this->remittance_number,
            'aratdar_id'         => $this->aratdar_id,
            'aratdar_name'       => $this->aratdar?->name,
            'aratdar_business'   => $this->aratdar?->business_name,
            'account_id'         => $this->account_id,
            'account_name'       => $this->account?->name,
            'amount'             => (float) $this->amount,
            'allocated_amount'   => (float) $this->allocated_amount,
            'unallocated_amount' => (float) $this->unallocated_amount,
            'payment_date'       => $this->payment_date?->format('Y-m-d'),
            'payment_method'     => $this->payment_method,
            'reference_number'   => $this->reference_number,
            'notes'              => $this->notes,
            'allocations'        => RemittanceAllocationResource::collection($this->whenLoaded('allocations')),
            'created_at'         => $this->created_at?->toIso8601String(),
        ];
    }
}