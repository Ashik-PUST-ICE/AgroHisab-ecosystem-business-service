<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
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
            's_mtr' => $this->s_mtr,
            'e_mtr' => $this->e_mtr,
            'quantity' => $this->quantity,
            'product_code' => $this->product_code,
            'serial_no' => $this->serial_no,
            'mac_address' => $this->mac_address,
            'remarks' => $this->remarks,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d,Y h:i A'),
            'stock_category' => $this->whenLoaded('stockCategory'),
            'stock_product' => $this->whenLoaded('stockProduct'),
            'transfer_product' => $this->whenLoaded('transferProduct'),
            'unit' => $this->whenLoaded('unit'),
        ];
    }
}
