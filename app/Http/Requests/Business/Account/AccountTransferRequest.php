<?php

namespace App\Http\Requests\Business\Account;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'to_account_id'   => ['required', 'integer', 'exists:accounts,id', 'different:from_account_id'],
            'amount'          => ['required', 'numeric', 'gt:0'],
            'txn_date'        => ['required', 'date'],
            'reference'       => ['nullable', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:500'],
        ];
    }
}