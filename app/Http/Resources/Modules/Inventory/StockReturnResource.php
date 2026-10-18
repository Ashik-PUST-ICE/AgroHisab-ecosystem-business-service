<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockReturnResource extends JsonResource
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
            'stock_category_id' => $this->stock_category_id,
            'stock_category' => $this->whenLoaded('stockCategory'),
            'stock_product_id' => $this->stock_product_id,
            'stock_product' => $this->whenLoaded('stockProduct'),
            'return_product_id' => $this->return_product_id,
            'return_product' => $this->whenLoaded('returnProduct'),
            's_mtr' => $this->s_mtr,
            'e_mtr' => $this->e_mtr,
            'quantity' => $this->quantity,
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit'),
            'product_code' => $this->product_code,
            'serial_no' => $this->serial_no,
            'mac_address' => $this->mac_address,
            'created_by' => $this->created_by,
            'returnable_type' => $this->returnable_type,
            'returnable_id' => $this->returnable_id,
            'returnable' => $this->whenLoaded('returnable'),
            'return_type' => $this->return_type,
            'reason' => $this->reason,
            'return_date' => $this->return_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d,Y h:i A'),
        ];
    }
}
