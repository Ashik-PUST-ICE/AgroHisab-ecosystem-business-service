<?php

namespace App\Http\Requests\Modules\Finance;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
            'central_account_id' => 'required|integer|exists:central_accounts,id',
            'operational_branch_id' => 'nullable|integer',
            'branch_id' => 'required|integer',
            'account_type_id' => 'required|integer|exists:account_types,id',
            'title' => 'required|string|max:255|unique:accounts,title,'.$this->route('account'),
            'account_holder_name' => 'required|string|max:255',
            'account_no' => 'required|string|max:255|unique:accounts,account_no,'.$this->route('account'),
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'routing_no' => 'nullable|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'opening_balance' => $this->opening_balance ?? 0,
        ]);
    }
}
