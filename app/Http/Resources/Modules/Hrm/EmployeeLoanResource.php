<?php

namespace App\Http\Resources\Modules\Hrm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLoanResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'loan_code' => $this->loan_code,
            'principal' => $this->principal,
            'outstanding' => $this->outstanding,
            'monthly_installment' => $this->monthly_installment,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
        ];
    }
}
