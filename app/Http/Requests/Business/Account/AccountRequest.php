<?php

namespace App\Http\Requests\Business\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Business\Account;

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
            'type'            => ['nullable', 'in:1,2,3,4,5,6'],
            'account_number'  => ['nullable', 'string', 'max:100'],
            'bank_name'       => ['nullable', 'string', 'max:255'],
            'branch'          => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
            'status'          => ['nullable', 'in:0,1'],
        ];
    }
}
