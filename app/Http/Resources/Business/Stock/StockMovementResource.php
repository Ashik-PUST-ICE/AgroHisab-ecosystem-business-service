<?php

namespace App\Http\Resources\Business\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'product_id'     => $this->product_id,
            'product_name'   => $this->product?->name,
            'type'           => $this->type,
            'type_label'     => stock_movement_type_label($this->type),
            'quantity'       => (float) $this->quantity,
            'unit_id'        => $this->unit_id,
            'unit_symbol'    => $this->unit?->symbol ?? $this->unit?->name,
            'market_id'      => $this->market_id,
            'market_name'    => $this->market?->name,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}