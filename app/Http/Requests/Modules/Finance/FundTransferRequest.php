<?php

namespace App\Http\Requests\Modules\Finance;

use Illuminate\Foundation\Http\FormRequest;

class FundTransferRequest extends FormRequest
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
            'account_from' => 'required|integer|exists:accounts,id',
            'account_to' => 'required|integer|exists:accounts,id',
            'transfer_date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'branch_id' => 'required|integer|exists:branches,id',
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
