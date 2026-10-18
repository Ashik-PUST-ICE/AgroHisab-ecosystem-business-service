<?php

namespace App\Http\Resources\Modules\Hrm\Leaves;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
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
            'balance' => $this->balance,
            'fiscal_year_id' => $this->fiscal_year_id,
            'fiscal_year' => $this->whenLoaded('fiscalYear'),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
