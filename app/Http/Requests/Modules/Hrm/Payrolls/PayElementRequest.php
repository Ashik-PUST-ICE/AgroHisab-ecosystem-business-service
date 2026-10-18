<?php

namespace App\Http\Requests\Modules\Hrm\Payrolls;

use Illuminate\Foundation\Http\FormRequest;

class PayElementRequest extends FormRequest
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
            'code' => 'required', 'string', 'max:255', 'unique:pay_elements,code'.$this->route('pay_element'),
            'name' => ['required', 'string', 'max:255'],
            'element_type' => ['required', 'string', 'max:255'],
            'taxable' => ['boolean'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'calculation_type' => ['string', 'max:255'],
            'formula' => ['nullable', 'string', 'max:1000'],
            'gl_account' => ['nullable', 'string', 'max:255'],
        ];

    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
            'taxable' => $this->boolean('taxable'),
        ]);
    }
}
