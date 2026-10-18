<?php

namespace App\Http\Requests\Business\Visit;

use Illuminate\Foundation\Http\FormRequest;

class CompleteVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string'],
            'display_condition' => ['nullable', 'string', 'max:255'],
            'display_quantity' => ['nullable', 'integer'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:2048'],
            'competitors' => ['nullable', 'array'],
            'competitors.*' => ['array'],
            'products' => ['nullable', 'array'],
            'products.*' => ['array'],
        ];
    }
}
