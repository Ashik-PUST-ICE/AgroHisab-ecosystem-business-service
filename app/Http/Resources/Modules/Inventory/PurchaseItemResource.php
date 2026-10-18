<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
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
            'purchase' => $this->whenLoaded('purchase'),
            'purchase_id' => $this->purchase_id,
            'serial_no' => $this->serial_no,
            'mac_address' => $this->mac_address,
            'brand' => $this->whenLoaded('brand'),
            'brand_id' => $this->brand_id,
            'warranty' => $this->warranty,
            'purchased_date' => $this->purchased_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
