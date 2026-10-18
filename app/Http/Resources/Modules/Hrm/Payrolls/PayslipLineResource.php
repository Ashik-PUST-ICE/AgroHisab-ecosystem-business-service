<?php

namespace App\Http\Resources\Modules\Hrm\Payrolls;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipLineResource extends JsonResource
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
            'payslip' => $this->whenLoaded('payslip'),
            'pay_element_id' => $this->pay_element_id,
            'pay_element' => $this->whenLoaded('payElement'),
            'label' => $this->label,
            'amount' => $this->amount,
            'is_earning' => $this->is_earning,
            'taxable' => $this->taxable,
            'quantity' => $this->quantity,
            'reference_id' => $this->reference_id,
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
        ];
    }
}
