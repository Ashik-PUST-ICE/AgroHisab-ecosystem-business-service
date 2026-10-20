<?php

namespace App\Http\Requests\Business\Payment;

use Illuminate\Foundation\Http\FormRequest;

class AratdarRemittanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aratdar_id'       => ['required', 'integer', 'exists:aratdars,id'],
            'account_id'       => ['required', 'integer', 'exists:accounts,id'],
            'amount'           => ['required', 'numeric', 'gt:0'],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['nullable', 'string', 'in:cash,bank,bkash,nagad,rocket,cheque,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'allocations'      => ['nullable', 'array'],
            'allocations.*.shipment_id' => ['required_with:allocations', 'integer', 'exists:shipments,id'],
            'allocations.*.amount'      => ['required_with:allocations', 'numeric', 'gt:0'],
            'allocations.*.notes'       => ['nullable', 'string', 'max:255'],
        ];
    }
}