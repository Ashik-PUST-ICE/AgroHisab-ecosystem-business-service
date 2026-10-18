<?php

namespace App\Http\Requests\Modules\Hrm\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class SalaryComponentRequest extends FormRequest
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
            'employment_id' => ['required', 'integer'],
            'pay_element_id' => ['required', 'exists:pay_elements,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'is_percentage' => ['boolean'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_percentage' => $this->boolean('is_percentage'),
        ]);
    }
}
