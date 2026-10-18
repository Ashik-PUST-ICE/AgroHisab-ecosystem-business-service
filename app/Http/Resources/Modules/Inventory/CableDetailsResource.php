<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CableDetailsResource extends JsonResource
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
            'product_code' => $this->product_code,
            'type' => $this->type,
            'stockable_type' => $this->stockable_type,
            'stockable_id' => $this->stockable_id,
            's_meter' => $this->s_mtr,
            'e_meter' => $this->e_mtr,
            'length' => $this->quantity,
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit'),
            'stock_product_id' => $this->stock_product_id,
            'stock_product' => $this->whenLoaded('stockProduct'),
            'stock_category_id' => $this->stock_category_id,
            'stock_category' => $this->whenLoaded('stockCategory'),
            'brand_id' => $this->brand_id,
            'brand' => $this->whenLoaded('brand'),
            'date' => $this->date,
            'admin_id' => $this->admin_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
