<?php

namespace App\Http\Requests\Modules\Finance;

use Illuminate\Foundation\Http\FormRequest;

class FinanceCategoryRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'status' => 'nullable|in:0,1',
        ];
    }
}
