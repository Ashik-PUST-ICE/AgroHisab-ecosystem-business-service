<?php

namespace App\Http\Resources\Modules\Hrm\Leaves;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeavePolicyResource extends JsonResource
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
            'fiscal_year_id' => $this->fiscal_year_id,
            'fiscal_year' => $this->whenLoaded('fiscalYear'),
            'branch_id' => $this->branch_id,
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => $this->whenLoaded('leaveType'),
            'accrual_rule' => $this->accrual_rule,
            'accrual_value' => $this->accrual_value,
            'max_balance' => $this->max_balance,
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
