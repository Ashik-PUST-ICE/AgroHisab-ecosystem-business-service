<?php

namespace App\Http\Resources\Business\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'purchase_number' => $this->purchase_number,
            'purchase_date'   => $this->purchase_date?->format('Y-m-d'),
            'supplier_id'     => $this->supplier_id,
            'supplier_name'   => $this->supplier?->name,
            'market_id'       => $this->market_id,
            'market_name'     => $this->market?->name,
            'total_amount'    => (float) $this->total_amount,
            'discount_amount' => (float) $this->discount_amount,
            'paid_amount'     => (float) $this->paid_amount,
            'due_amount'      => (float) $this->due_amount,
            'status'          => (int) $this->status,
            'status_label'    => purchase_status_label($this->status),
            'notes'           => $this->notes,
            'items'           => PurchaseItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}