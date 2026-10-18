<?php

namespace App\Http\Resources\Modules\Hrm\Leaves;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'paid' => $this->paid,
            'requires_approval' => $this->requires_approval,
            'carry_forward' => $this->carry_forward,
            'max_days_year' => $this->max_days_year,
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
