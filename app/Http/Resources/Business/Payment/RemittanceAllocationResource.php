<?php

namespace App\Http\Resources\Business\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RemittanceAllocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'aratdar_remittance_id'  => $this->aratdar_remittance_id,
            'shipment_id'            => $this->shipment_id,
            'shipment_number'        => $this->shipment?->shipment_number,
            'amount'                 => (float) $this->amount,
            'allocation_date'        => $this->allocation_date?->format('Y-m-d'),
            'notes'                  => $this->notes,
            'created_at'             => $this->created_at?->toIso8601String(),
        ];
    }
}