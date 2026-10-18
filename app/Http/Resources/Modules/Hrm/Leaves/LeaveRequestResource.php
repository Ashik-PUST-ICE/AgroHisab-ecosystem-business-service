<?php

namespace App\Http\Resources\Modules\Hrm\Leaves;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
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
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => $this->whenLoaded('leaveType'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'fiscal_year_id' => $this->fiscal_year_id,
            'fiscal_year' => $this->whenLoaded('fiscalYear'),
            'reason' => $this->reason,
            'address' => $this->address,
            'applied_to' => $this->applied_to,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
