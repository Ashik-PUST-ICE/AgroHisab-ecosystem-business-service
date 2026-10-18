<?php

namespace App\Http\Resources\Modules\Inventory\Purchases;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => format_smart_number($this->quantity),
            'total_amount' => format_currency($this->total_amount),
            'paid_amount' => format_currency($this->paid_amount),
            'due_amount' => format_currency($this->due_amount),
            'purchase_date' => $this->purchase_date?->format('j M, Y'),
            'invoice_no' => $this->invoice_no,
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
            'requisition' => $this->whenLoaded('requisition'),
        ];
    }
}
