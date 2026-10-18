<?php

namespace App\Http\Resources\Modules\Hrm\Payrolls;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayElementResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'element_type' => $this->element_type,
            'taxable' => $this->taxable,
            'default_amount' => $this->default_amount,
            'calculation_type' => $this->calculation_type,
            'formula' => $this->formula,
            'gl_account' => $this->gl_account,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('F d, Y h:i A'),
            'updated_at' => $this->updated_at?->format('F d, Y h:i A'),
        ];
    }
}
