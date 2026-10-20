<?php

namespace App\Http\Resources\Business\Shipment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'shipment_number'       => $this->shipment_number,
            'aratdar_id'            => $this->aratdar_id,
            'aratdar_name'          => $this->aratdar?->name,
            'transporter_id'        => $this->transporter_id,
            'transporter_name'      => $this->transporter?->name,
            'market_id'             => $this->market_id,
            'market_name'           => $this->market?->name,
            'dispatch_date'         => $this->dispatch_date?->format('Y-m-d'),
            'expected_arrival_date' => $this->expected_arrival_date?->format('Y-m-d'),
            'vehicle_number'        => $this->vehicle_number,
            'freight_amount'        => (float) $this->freight_amount,
            'freight_paid_by'       => $this->freight_paid_by,
            'status'                => (int) $this->status,
            'status_label'          => shipment_status_label($this->status),
            'notes'                 => $this->notes,
            'expense_breakdown'     => $this->expense_breakdown ?? [],
            'items'                 => ShipmentItemResource::collection($this->whenLoaded('items')),
            'created_at'            => $this->created_at?->toDateTimeString(),
            'updated_at'            => $this->updated_at?->toDateTimeString(),
        ];
    }
}