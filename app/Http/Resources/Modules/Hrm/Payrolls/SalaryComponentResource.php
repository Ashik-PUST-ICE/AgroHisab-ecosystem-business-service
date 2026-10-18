<?php

namespace App\Http\Resources\Modules\Hrm\Payrolls;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryComponentResource extends JsonResource
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
            'employment_id' => $this->employment_id,
            'pay_element_id' => $this->pay_element_id,
            'pay_element' => $this->whenLoaded('payElement'),
            'amount' => $this->amount,
            'is_percentage' => $this->is_percentage,
            'effective_from' => $this->effective_from?->format('Y-m-d'),
            'effective_to' => $this->effective_to?->format('Y-m-d'),
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
        ];
    }
}
