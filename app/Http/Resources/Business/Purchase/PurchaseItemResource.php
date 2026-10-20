<?php

namespace App\Http\Resources\Business\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => $this->product?->name,
            'unit_id'      => $this->unit_id,
            'unit_name'    => $this->unit?->name,
            'bag_count'    => (int) $this->bag_count,
            'gross_weight' => (float) $this->gross_weight,
            'tare_weight'  => (float) $this->tare_weight,
            'net_weight'   => (float) $this->net_weight,
            'unit_rate'    => (float) $this->unit_rate,
            'line_total'   => (float) $this->line_total,
            'notes'        => $this->notes,
        ];
    }
}