<?php

namespace App\Http\Resources\Business\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'variety' => $this->variety,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'default_unit_id' => $this->default_unit_id,
            'default_unit' => new UnitResource($this->whenLoaded('defaultUnit')),
            'description' => $this->description,
            'status' => (int) $this->status,
            'status_label' => \getStatus($this->status),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}