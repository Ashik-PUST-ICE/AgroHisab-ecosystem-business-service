<?php

namespace App\Http\Requests\Modules\Hrm\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class PayslipRequest extends FormRequest
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
            'payroll_run_id' => ['required', 'exists:payroll_runs,id'],
            'employee_id' => ['required', 'integer'],
            'employment_id' => ['nullable', 'integer'],
            'gross_pay' => ['required', 'numeric', 'min:0'],
            'total_deductions' => ['required', 'numeric', 'min:0'],
            'net_pay' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['string', 'size:3'],
            'issued_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'currency_code' => $this->currency_code ?? 'BDT',
        ]);
    }
}
