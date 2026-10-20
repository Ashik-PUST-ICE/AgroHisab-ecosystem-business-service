<?php

namespace App\Http\Requests\Business\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class SupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id'       => ['required', 'integer', 'exists:accounts,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_date'     => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}