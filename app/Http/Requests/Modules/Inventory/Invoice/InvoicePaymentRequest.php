<?php

namespace App\Http\Requests\Modules\Inventory\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoicePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer',
            'invoice_id' => 'nullable|integer|exists:invoices,id',
            'account_id' => 'nullable|integer|exists:accounts,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
