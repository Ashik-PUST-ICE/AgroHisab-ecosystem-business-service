<?php

namespace App\Http\Requests\Modules\Finance;

use Illuminate\Foundation\Http\FormRequest;

class ConveyanceRequest extends FormRequest
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
            'purpose' => 'required|string|max:255',
            'user_id' => 'required|integer',
            'voucher' => 'nullable|string|max:255',
            'account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'biling_date' => 'required|date',
            'description' => 'nullable|string',
            'finance_category_id' => 'required|exists:finance_categories,id',
        ];
    }

    protected function prepareForValidation()
    {
        // Modify input before validation
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
