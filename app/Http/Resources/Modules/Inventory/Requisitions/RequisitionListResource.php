<?php

namespace App\Http\Resources\Modules\Inventory\Requisitions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionListResource extends JsonResource
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
            'requisition_code' => $this->requisition_code,
            'stock_category' => $this->whenLoaded('stockCategory'),
            'product' => $this->whenLoaded('product'),
            'purpose' => $this->purpose,
            'brand' => $this->whenLoaded('brand'),
            'brand_id' => $this->brand_id,
            'product_code' => $this->product_code,
            'unit' => $this->whenLoaded('unit'),
            'quantity' => format_smart_number($this->quantity),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'approved_by' => user()->getUser($this->approved_by, ['id', 'full_name', 'image', 'unique_id']),
            'created_by' => user()->getUser($this->created_by, ['id', 'full_name', 'image', 'unique_id']),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
        ];
    }
}
