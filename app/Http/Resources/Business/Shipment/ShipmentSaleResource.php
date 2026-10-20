<?php

namespace App\Http\Resources\Business\Shipment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentSaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'sale_number'       => $this->sale_number,
            'shipment_id'       => $this->shipment_id,
            'shipment_item_id'  => $this->shipment_item_id,
            'product_id'        => $this->product_id,
            'product_name'      => $this->product?->name,
            'unit_id'           => $this->unit_id,
            'unit_name'         => $this->unit?->name,
            'sale_date'         => $this->sale_date?->format('Y-m-d'),
            'quantity'          => (float) $this->quantity,
            'unit_rate'         => (float) $this->unit_rate,
            'gross_amount'      => (float) $this->gross_amount,
            'discount_amount'   => (float) $this->discount_amount,
            'net_amount'        => (float) $this->net_amount,
            'commission_type'   => $this->commission_type,
            'commission_label'  => commission_type_label($this->commission_type),
            'commission_rate'   => (float) $this->commission_rate,
            'commission_amount' => (float) $this->commission_amount,
            'net_proceeds'      => (float) $this->net_proceeds,
            'buyer_name'        => $this->buyer_name,
            'buyer_phone'       => $this->buyer_phone,
            'notes'             => $this->notes,
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}