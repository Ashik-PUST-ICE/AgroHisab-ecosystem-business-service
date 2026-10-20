<?php

namespace App\Http\Resources\Business\Shipment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'purchase_item_id'  => $this->purchase_item_id,
            'product_id'        => $this->product_id,
            'product_name'      => $this->product?->name,
            'unit_id'           => $this->unit_id,
            'unit_name'         => $this->unit?->name,
            'bag_count'         => (int) $this->bag_count,
            'dispatched_weight' => (float) $this->dispatched_weight,
            'received_weight'   => (float) $this->received_weight,
            'shortage_weight'   => (float) $this->shortage_weight,
            'unit_rate'         => (float) $this->unit_rate,
            'notes'             => $this->notes,
        ];
    }
}