<?php

namespace App\Http\Resources\Modules\Hrm\Holidays;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
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
            'holiday_group_id' => $this->holiday_group_id,
            'holiday_group' => $this->whenLoaded('holidayGroup'),
            'name' => $this->name,
            'short_name' => $this->short_name,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'scope_type' => $this->scope_type,
            'scope_id' => $this->scope_id,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d,Y h:i A'),
        ];
    }
}
