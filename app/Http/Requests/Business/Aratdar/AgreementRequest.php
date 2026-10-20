<?php

namespace App\Http\Requests\Business\Aratdar;

use Illuminate\Foundation\Http\FormRequest;

class AgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commission_type'  => ['required', 'integer', 'in:1,2'],  // 1=percentage, 2=fixed per bag
            'commission_value' => ['required', 'numeric', 'gt:0'],
            'agreement_start'  => ['required', 'date'],
            'agreement_end'    => ['nullable', 'date', 'after_or_equal:agreement_start'],
        ];
    }

    public function messages(): array
    {
        return [
            'commission_type.in' => 'commission_type: 1 = Percentage, 2 = Fixed Per Bag',
        ];
    }
}