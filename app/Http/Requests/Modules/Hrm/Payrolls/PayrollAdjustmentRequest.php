<?php

namespace App\Http\Requests\Modules\Hrm\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class PayrollAdjustmentRequest extends FormRequest
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
            'payslip_id' => ['nullable', 'exists:payslips,id'],
            'payroll_run_id' => ['nullable', 'exists:payroll_runs,id'],
            'employee_id' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
