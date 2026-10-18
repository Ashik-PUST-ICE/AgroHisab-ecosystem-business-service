<?php

namespace App\Http\Requests\Modules\Hrm\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRunRequest extends FormRequest
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
        $rules = [
            'run_code' => 'required', 'string', 'max:255', 'unique:payroll_runs,run_code'.$this->route('payroll_run'),
            'period_start' => ['required', 'date'],
            'period_end' => ['nullable', 'date', 'after:period_start'],

        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
