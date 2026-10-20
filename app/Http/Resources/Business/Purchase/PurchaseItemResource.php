<?php

namespace App\Http\Resources\Business\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'product_id'      => $this->product_id,
            'product_name'    => $this->product?->name,
            'unit_id'         => $this->unit_id,
            'unit_name'       => $this->unit?->name,
            'bag_count'       => (int) $this->bag_count,
            'actual_weight'   => (float) ($this->actual_weight ?: $this->gross_weight),
            'deducted_weight' => (float) ($this->deducted_weight ?: $this->tare_weight),
            'gross_weight'    => (float) $this->gross_weight,
            'tare_weight'     => (float) $this->tare_weight,
            'net_weight'      => (float) $this->net_weight,
            'mon_rate'        => $this->mon_rate !== null ? (float) $this->mon_rate : null,
            'mon_size'        => (float) ($this->mon_size ?: 40.00),
            'unit_rate'       => (float) $this->unit_rate,
            'line_total'      => (float) $this->line_total,
            'notes'           => $this->notes,
        ];
    }
}