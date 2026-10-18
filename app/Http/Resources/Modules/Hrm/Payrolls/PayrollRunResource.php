<?php

namespace App\Http\Resources\Modules\Hrm\Payrolls;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
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
            'run_code' => $this->run_code,
            'period_start' => $this->period_start?->format('Y-m-d'),
            'period_end' => $this->period_end?->format('Y-m-d'),
            'status' => $this->status,
            'formatted_status' => $this->status?->label(),
            'processed_at' => $this->processed_at?->format('F d, Y h:i A'),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
            'payslips_count' => $this->whenLoaded('payslips', fn () => $this->payslips->count()),
        ];
    }
}
