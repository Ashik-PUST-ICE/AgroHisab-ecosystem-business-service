<?php

namespace App\Http\Resources\Business\Aratdar;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AratdarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'business_name'    => $this->business_name,
            'phone'            => $this->phone,
            'address'          => $this->address,
            'market'           => $this->market,
            'district'         => $this->district,
            'notes'            => $this->notes,
            'agreement_start'  => $this->agreement_start?->toDateString(),
            'agreement_end'    => $this->agreement_end?->toDateString(),
            'commission_type'  => (int) $this->commission_type,
            'commission_type_label' => commission_type_label($this->commission_type),
            'commission_value' => (float) $this->commission_value,
            'commission_base'  => $this->commission_base,
            'status'           => (int) $this->status,
            'status_label'     => status_label($this->status),
            'created_at'       => $this->created_at?->toDateTimeString(),
            'updated_at'       => $this->updated_at?->toDateTimeString(),
        ];
    }
}
