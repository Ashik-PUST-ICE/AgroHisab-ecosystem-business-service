<?php

namespace App\Http\Requests\Modules\Hrm;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeLoanRequest extends FormRequest
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
            'employee_id' => ['required', 'integer'],
            'loan_code' => 'nullable', 'string', 'max:255', 'unique:employee_loans,loan_code'.$this->route('employee-loan'),
            'principal' => ['required', 'numeric', 'min:0'],
            'outstanding' => ['nullable', 'numeric', 'min:0'],
            'monthly_installment' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],

        ];
    }
}
