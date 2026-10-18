<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectedProductResource extends JsonResource
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
            'vendor_id' => $this->vendor_id,
            'vendor' => $this->whenLoaded('vendor'),
            's_mtr' => $this->s_mtr,
            'e_mtr' => $this->e_mtr,
            'product_code' => $this->product_code,
            'quantity' => $this->quantity,
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit'),
            'amount' => $this->amount,
            'collected_date' => $this->collected_date?->format('Y-m-d'),
            'created_by' => $this->created_by,
            'warranty' => $this->warranty,
            'comment' => $this->comment,
            'collectable_type' => $this->collectable_type,
            'collectable_id' => $this->collectable_id,
            'collectable' => $this->whenLoaded('collectable'),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d,Y h:i A'),
        ];
    }
}
