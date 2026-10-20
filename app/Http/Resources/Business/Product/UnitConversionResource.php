<?php

namespace App\Http\Resources\Business\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitConversionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_unit_id' => $this->from_unit_id,
            'from_unit' => new UnitResource($this->whenLoaded('fromUnit')),
            'to_unit_id' => $this->to_unit_id,
            'to_unit' => new UnitResource($this->whenLoaded('toUnit')),
            'factor' => (float) $this->factor,
            'notes' => $this->notes,
            'status' => (int) $this->status,
            'status_label' => \getStatus($this->status),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}