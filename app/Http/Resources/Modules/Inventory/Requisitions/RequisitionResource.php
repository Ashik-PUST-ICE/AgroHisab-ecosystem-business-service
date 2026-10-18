<?php

namespace App\Http\Resources\Modules\Inventory\Requisitions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionResource extends JsonResource
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
            'stock_category_id' => $this->stock_category_id,
            'product_id' => $this->product_id,
            'purpose' => $this->purpose,
            'vendor_id' => $this->vendor_id,
            'brand_id' => $this->brand_id,
            'product_code' => $this->product_code,
            'unit_id' => $this->unit_id,
            'quantity' => format_smart_number($this->quantity),
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'remarks' => $this->remarks,
            'created_by' => $this->created_by,
            'status' => $this->status,
            'comment' => $this->comment,
        ];
    }
}
