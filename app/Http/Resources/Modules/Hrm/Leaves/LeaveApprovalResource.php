<?php

namespace App\Http\Resources\Modules\Hrm\Leaves;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveApprovalResource extends JsonResource
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
            'leave_request_id' => $this->leave_request_id,
            'leave_request' => $this->whenLoaded('leaveRequest'),
            'approver_id' => $this->approver_id,
            'action' => $this->action,
            'formatted_action' => $this->action?->label(),
            'action_at' => $this->action_at?->format('F d,Y h:i A'),
            'note' => $this->note,
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d,Y h:i A'),
        ];
    }
}
