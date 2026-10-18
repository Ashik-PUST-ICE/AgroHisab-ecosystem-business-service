<?php

namespace App\Http\Requests\Modules\Inventory\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|in:product,service',
            'network_id' => 'nullable|integer|required_if:invoice_type,service',
            'address_id' => 'nullable|integer',
            'recurring_date' => 'nullable|date',
            'subtotal' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'additional_amount' => 'nullable|numeric|min:0',
            'is_enabled_vat' => 'boolean',
            'vat_percentage' => 'nullable|numeric|min:0|max:100|required_if:is_enabled_vat,true',
            'note' => 'nullable|string',
            'terms_condition' => 'nullable|string',
            'bank_details' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.package_id' => 'nullable|integer||required_if:invoice_type,service',
            'items.*.product_id' => 'nullable|integer|required_if:invoice_type,product',
            'items.*.title' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }


     protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }


}
