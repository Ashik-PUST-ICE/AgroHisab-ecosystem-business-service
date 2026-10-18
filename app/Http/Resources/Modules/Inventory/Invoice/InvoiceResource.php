<?php

namespace App\Http\Resources\Modules\Inventory\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'user_id' => $this->user_id,
            'invoice_date' => $this->invoice_date->format('Y-m-d'),
            'invoice_type' => $this->invoice_type,
            'network_id' => $this->network_id,
            'address_id' => $this->address_id,
            'recurring_date' => $this->recurring_date ? $this->recurring_date->format('Y-m-d') : null,
            'created_by' => $this->created_by,
            'subtotal' => (float) $this->subtotal,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'discount_amount' => (float) $this->discount_amount,
            'additional_amount' => (float) $this->additional_amount,
            'is_enabled_vat' => (bool) $this->is_enabled_vat,
            'vat_percentage' => (float) $this->vat_percentage,
            'vat_amount' => (float) $this->vat_amount,
            'total_amount' => (float) $this->total_amount,
            'note' => $this->note,
            'terms_condition' => $this->terms_condition,
            'bank_details' => $this->bank_details,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'invoice_id' => $item->invoice_id,
                        'package_id' => $item->package_id,
                        'package' => $item->package ? [
                            'id' => $item->package->id,
                            'name' => $item->package->name,
                            'price' => $item->package->price,
                        ] : null,
                        'product_id' => $item->product_id,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                        ] : null,
                        'title' => $item->title,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $item->updated_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
