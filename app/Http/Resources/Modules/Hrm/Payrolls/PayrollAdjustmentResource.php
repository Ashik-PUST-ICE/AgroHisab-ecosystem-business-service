<?php

namespace App\Http\Resources\Modules\Hrm\Payrolls;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollAdjustmentResource extends JsonResource
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
            'payslip_id' => $this->payslip_id,
            'payroll_run_id' => $this->payroll_run_id,
            'employee_id' => $this->employee_id,
            'reason' => $this->reason,
            'amount' => $this->amount,
            'created_by' => $this->created_by,
            'payslip' => $this->whenLoaded('payslip'),
            'payroll_run' => $this->whenLoaded('payrollRun'),
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
        ];
    }
}
