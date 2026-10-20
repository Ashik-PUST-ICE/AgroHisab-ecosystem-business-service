<?php

namespace App\Http\Resources\Business\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'adjustment_number' => $this->adjustment_number,
            'product_id'        => $this->product_id,
            'product'           => $this->whenLoaded('product', fn() => [
                'id'   => $this->product->id,
                'name' => $this->product->name,
                'code' => $this->product->code ?? null,
            ]),
            'market_id'         => $this->market_id,
            'market'            => $this->whenLoaded('market', fn() => [
                'id'   => $this->market->id,
                'name' => $this->market->name,
            ]),
            'unit_id'           => $this->unit_id,
            'unit'              => $this->whenLoaded('unit', fn() => [
                'id'     => $this->unit->id,
                'name'   => $this->unit->name,
                'symbol' => $this->unit->symbol,
            ]),
            'loss_type'         => $this->loss_type,
            'loss_type_label'   => $this->loss_type_label,
            'quantity'          => (float) $this->quantity,
            'adjustment_date'   => $this->adjustment_date?->format('Y-m-d'),
            'reason'            => $this->reason,
            'notes'             => $this->notes,
            'created_by'        => $this->created_by,
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }
}