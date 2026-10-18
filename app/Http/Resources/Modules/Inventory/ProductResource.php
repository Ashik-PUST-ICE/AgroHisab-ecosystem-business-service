<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'stock_category' => $this->whenLoaded('stockCategory', function () {
                return [
                    'id' => $this->stockCategory->id,
                    'name' => $this->stockCategory->name,
                ];
            }),
            'stock_category_id' => $this->stock_category_id,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                ];
            }),
            'unit_id' => $this->unit_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image ? assetUrl($this->image) : null,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
