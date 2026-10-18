<?php

namespace App\Http\Requests\Modules\Finance;

use App\Enums\Commons\TransactionType\TransactionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'type' => ['nullable', Rule::enum(TransactionTypeEnum::class)],
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0.01|max:999999999999.99|decimal:0,2',
            'transaction_date' => 'nullable|date|before_or_equal:today',
            'transactionable_type' => 'nullable|string',
            'transactionable_id' => 'nullable|integer|min:1',
        ];
    }
}
