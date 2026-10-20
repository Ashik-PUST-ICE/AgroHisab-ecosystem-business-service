<?php

namespace App\Http\Requests\Business\Payment;

use Illuminate\Foundation\Http\FormRequest;

class AllocateRemittanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allocations'               => ['required', 'array', 'min:1'],
            'allocations.*.shipment_id' => ['required', 'integer', 'exists:shipments,id'],
            'allocations.*.amount'      => ['required', 'numeric', 'gt:0'],
            'allocations.*.notes'       => ['nullable', 'string', 'max:255'],
        ];
    }
}