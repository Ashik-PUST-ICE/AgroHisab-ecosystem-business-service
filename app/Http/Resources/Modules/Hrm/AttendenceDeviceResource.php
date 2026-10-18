<?php

namespace App\Http\Resources\Modules\Hrm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendenceDeviceResource extends JsonResource
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
            'branch_id' => $this->branch_id,
            'vendor' => $this->vendor,
            'model' => $this->model,
            'ip_address' => $this->ip_address,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
