<?php

namespace App\Http\Requests\Modules\Finance;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'bill_no' => 'required|string|unique:expenses,bill_no,'.$this->route('expense'),
            'expense_date' => 'required|date',
            'account_id' => 'required|integer|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'voucher' => 'nullable|string|max:255',
            'payment_status' => 'required|integer|in:0,1,2',
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
