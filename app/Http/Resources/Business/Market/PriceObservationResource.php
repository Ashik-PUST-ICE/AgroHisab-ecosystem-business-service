<?php

namespace App\Http\Resources\Business\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceObservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'market_id'     => $this->market_id,
            'market_name'   => $this->market?->name,
            'product_id'    => $this->product_id,
            'product_name'  => $this->product?->name,
            'unit_id'       => $this->unit_id,
            'unit_symbol'   => $this->unit?->symbol ?? $this->unit?->name,
            'observed_date' => $this->observed_date?->toDateString(),
            'min_price'     => $this->min_price !== null ? (float) $this->min_price : null,
            'max_price'     => $this->max_price !== null ? (float) $this->max_price : null,
            'avg_price'     => $this->avg_price !== null ? (float) $this->avg_price : null,
            'source'        => $this->source,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}