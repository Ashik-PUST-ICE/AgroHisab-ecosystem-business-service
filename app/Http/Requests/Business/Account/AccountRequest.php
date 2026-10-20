<?php

namespace App\Http\Requests\Business\Account;

use App\Models\Business\Account;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('account') instanceof Account;

        return [
            'name'            => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'type'            => ['nullable', 'in:' . ACCOUNT_CASH . ',' . ACCOUNT_BANK . ',' . ACCOUNT_BKASH . ',' . ACCOUNT_NAGAD . ',' . ACCOUNT_ROCKET . ',' . ACCOUNT_OTHER],
            'account_number'  => ['nullable', 'string', 'max:100'],
            'bank_name'       => ['nullable', 'string', 'max:255'],
            'branch'          => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
            'status'          => ['nullable', 'in:' . STATUS_ACTIVE . ',' . STATUS_INACTIVE],
        ];
    }
}