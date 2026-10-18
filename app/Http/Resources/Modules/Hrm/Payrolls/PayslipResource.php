<?php

namespace App\Http\Resources\Modules\Hrm\Payrolls;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
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
            'payroll_run_id' => $this->payroll_run_id,
            'payroll_run' => $this->whenLoaded('payrollRun'),
            'employee_id' => $this->employee_id,
            'employment_id' => $this->employment_id,
            'gross_pay' => $this->gross_pay,
            'total_deductions' => $this->total_deductions,
            'net_pay' => $this->net_pay,
            'currency_code' => $this->currency_code,
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'issued_at' => $this->issued_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
        ];
    }
}
