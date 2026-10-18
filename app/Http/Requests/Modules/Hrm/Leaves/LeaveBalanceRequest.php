<?php

namespace App\Http\Requests\Modules\Hrm\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class LeaveBalanceRequest extends FormRequest
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
            'user_id' => ['required', 'integer'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'balance' => ['required', 'numeric', 'min:0'],
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
        ];
    }
}
