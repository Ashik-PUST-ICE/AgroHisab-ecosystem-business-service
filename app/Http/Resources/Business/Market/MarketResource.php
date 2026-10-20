<?php

namespace App\Http\Resources\Business\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'district'      => $this->district,
            'address'       => $this->address,
            'type'          => (int) $this->type,
            'type_label'    => market_type_label($this->type),
            'contact_notes' => $this->contact_notes,
            'status'        => (int) $this->status,
            'status_label'  => status_label($this->status),
            'created_at'    => $this->created_at?->toDateTimeString(),
            'updated_at'    => $this->updated_at?->toDateTimeString(),
        ];
    }
}