<?php

namespace App\Http\Resources\Business\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'market'       => $this->market,
            'district'     => $this->district,
            'notes'        => $this->notes,
            'status'       => (int) $this->status,
            'status_label' => status_label($this->status),
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}