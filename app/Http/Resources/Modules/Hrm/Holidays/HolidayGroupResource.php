<?php

namespace App\Http\Resources\Modules\Hrm\Holidays;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayGroupResource extends JsonResource
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
            'company_id' => $this->company_id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d,Y h:i A'),
        ];
    }
}
