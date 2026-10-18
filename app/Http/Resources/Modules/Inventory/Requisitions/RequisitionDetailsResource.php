<?php

namespace App\Http\Resources\Modules\Inventory\Requisitions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionDetailsResource extends JsonResource
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
            'stock_category' => $this->whenLoaded('stockCategory', function () {
                return [
                    'id' => $this->stockCategory->id,
                    'name' => $this->stockCategory->name,
                ];
            }),
            'product' => $this->whenLoaded('product'),
            'purpose' => $this->purpose,
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ];
            }),
            'product_code' => $this->product_code,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                ];
            }),
            'quantity' => format_smart_number($this->quantity),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'approved_by' => user()->getUser($this->approved_by, ['id', 'full_name', 'image', 'unique_id']),
            'created_by' => user()->getUser($this->created_by, ['id', 'full_name', 'image', 'unique_id']),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'full_name' => $this->vendor->full_name,
                ];
            }),
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'comment' => $this->comment,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
