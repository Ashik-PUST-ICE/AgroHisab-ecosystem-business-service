<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOutResource extends JsonResource
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
            'stock_category' => $this->whenLoaded('stockCategory'),
            'stock_category_id' => $this->stock_category_id,
            'stock_product' => $this->whenLoaded('stockProduct'),
            'stock_product_id' => $this->stock_product_id,
            'stock_history' => $this->whenLoaded('stockHistory'),
            'stock_history_id' => $this->stock_history_id,
            'network_id' => $this->network_id,
            's_mtr' => $this->s_mtr,
            'e_mtr' => $this->e_mtr,
            'quantity' => $this->quantity,
            'color' => $this->color,
            'price' => $this->unit_price,
            'brand' => $this->whenLoaded('brand'),
            'brand_id' => $this->brand_id,
            'unit' => $this->whenLoaded('unit'),
            'unit_id' => $this->unit_id,
            'serial_no' => $this->serial_no,
            'mac_address' => $this->mac_address,
            'product_code' => $this->product_code,
            'stock_out_date' => $this->stock_out_date,
            'assignable_type' => $this->assignable_type,
            'assignable_id' => $this->assignable_id,
            'assignable' => $this->whenLoaded('assignable'),
            'remarks' => $this->remarks,
            'is_returned' => $this->is_returned,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
