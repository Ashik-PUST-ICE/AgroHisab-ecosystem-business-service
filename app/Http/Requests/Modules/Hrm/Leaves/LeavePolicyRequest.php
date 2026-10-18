<?php

namespace App\Http\Requests\Modules\Hrm\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class LeavePolicyRequest extends FormRequest
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
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'branch_id' => ['required', 'integer'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'accrual_rule' => ['required', 'string', 'max:255'],
            'accrual_value' => ['required', 'numeric', 'min:0'],
            'max_balance' => ['required', 'numeric', 'min:0'],

        ];
    }
}
