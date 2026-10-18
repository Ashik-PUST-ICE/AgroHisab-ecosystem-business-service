<?php

namespace App\Http\Requests\Modules\Hrm\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class PayslipLineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payslip_id' => ['required', 'exists:payslips,id'],
            'pay_element_id' => ['nullable', 'exists:pay_elements,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_earning' => ['boolean'],
            'taxable' => ['boolean'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'reference_id' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_earning' => $this->boolean('is_earning', false),
            'taxable' => $this->boolean('taxable', true),
        ]);
    }
}
