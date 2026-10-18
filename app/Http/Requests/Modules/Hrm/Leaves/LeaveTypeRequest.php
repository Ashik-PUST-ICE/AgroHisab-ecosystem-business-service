<?php

namespace App\Http\Requests\Modules\Hrm\Leaves;

use Illuminate\Foundation\Http\FormRequest;

class LeaveTypeRequest extends FormRequest
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
            'company_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:10', 'unique:leave_types,code,'.$this->route('leave_type')],
            'name' => ['required', 'string', 'max:255'],
            'paid' => ['nullable', 'boolean'],
            'requires_approval' => ['nullable', 'boolean'],
            'carry_forward' => ['nullable', 'boolean'],
            'max_days_year' => ['required', 'integer', 'min:0'],

        ];
    }
}
