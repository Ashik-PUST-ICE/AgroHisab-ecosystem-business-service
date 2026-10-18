<?php

namespace App\Http\Resources\Modules\Hrm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
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
            'title' => $this->title,
            'work_schedule_id' => $this->work_schedule_id,
            'work_schedule' => $this->whenLoaded('workSchedule'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_minutes' => $this->break_minutes,
            'is_night_shift' => $this->is_night_shift,
            'color' => $this->color,
            'effective_at' => $this->effective_at,
            'flexible_time' => $this->flexible_time,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
