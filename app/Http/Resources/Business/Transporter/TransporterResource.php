<?php

namespace App\Http\Resources\Business\Transporter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransporterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'truck_number' => $this->truck_number,
            'driver_name'  => $this->driver_name,
            'driver_phone' => $this->driver_phone,
            'address'      => $this->address,
            'notes'        => $this->notes,
            'status'       => (int) $this->status,
            'status_label' => status_label($this->status),
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}