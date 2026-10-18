<?php

namespace App\Http\Resources\Modules\Inventory\Purchases;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warranty' => $this->warranty,
            'quantity' => format_smart_number($this->quantity),
            'unit_price' => format_currency($this->unit_price),
            'total_price' => format_currency($this->total_price),
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_amount' => format_currency($this->discount_amount),
            'tax_amount' => format_currency($this->tax_amount),
            'additional_charge' => format_currency($this->additional_charge),
            'total_amount' => format_currency($this->total_amount),
            'paid_amount' => format_currency($this->paid_amount),
            'due_amount' => format_currency($this->due_amount),
            'purchase_date' => $this->purchase_date?->format('j M, Y'),
            'invoice_no' => $this->invoice_no,
            'invoice_document' => $this->invoice_document,
            'product_code' => $this->product_code,
            'purchase_by' => user()->getUser($this->purchase_by, ['id', 'full_name', 'image', 'unique_id']),

            // Relations
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'full_name' => $this->vendor->full_name,
                ];
            }),
            'brand' => $this->whenLoaded('brand'),
            'unit' => $this->whenLoaded('unit'),
            'account' => $this->whenLoaded('account'),
            'product' => $this->whenLoaded('product'),
            'requisition' => $this->whenLoaded('requisition', function () {
                return [
                    'id' => $this->requisition->id,
                    'requisition_code' => $this->requisition->requisition_code,
                ];
            }),
            'purchaseItems' => $this->whenLoaded('purchaseItems'),
        ];
    }
}
