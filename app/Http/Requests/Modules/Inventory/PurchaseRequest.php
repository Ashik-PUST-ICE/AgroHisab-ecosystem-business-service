<?php

namespace App\Http\Requests\Modules\Inventory;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = Product::find($this->product_id);
        $itemsRule = $product && $product->is_serial_mandatory ? 'required' : 'nullable';
        $itemMaxRules = $product && $product->is_serial_mandatory ? 'max:'.$this->quantity : '';

        return [
            'requisition_id' => 'required|integer|exists:requisitions,id',
            'warranty' => 'nullable|string',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'discount_type' => 'nullable|string|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'additional_charge' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',
            'purchase_date' => 'required|date',
            'invoice_no' => 'required|string',
            'invoice_document' => 'nullable|string',
            'product_code' => 'nullable|string',
            'account_id' => [
                'nullable',
                'integer',
                'exists:accounts,id',
                Rule::requiredIf(function () {
                    return $this->paid_amount > 0;
                }),
            ],

            'items' => [$itemsRule, 'array', 'min:0', $itemMaxRules],
            'items.*.serial_no' => [$itemsRule, 'string', 'unique:stocks,serial_no'],
            'items.*.mac_address' => ['nullable', 'string', 'unique:stocks,mac_address'],
            'items.*.brand_id' => [$itemsRule, 'integer', 'exists:brands,id'],

            's_mtr' => ['nullable', 'numeric', Rule::requiredIf(function () use ($product) {
                return $product && $product->stock_category_id == env('FIBER_CATEGORY_ID');
            })],
            'e_mtr' => ['nullable', 'numeric', Rule::requiredIf(function () use ($product) {
                return $product && $product->stock_category_id == env('FIBER_CATEGORY_ID');
            })],

        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'paid_amount' => $this->input('paid_amount', 0) ?? 0,
            'items' => isset($this->items) ? array_values(array_filter($this->items)) : [],
            'purchase_date' => $this->input('purchase_date') ? date('Y-m-d', strtotime($this->input('purchase_date'))) : now(),
        ]);
    }

    public function attributes(): array
    {
        return [
            'requisition_id' => 'Requisition',
            'account_id' => 'Account',
            'items.*.serial_no' => 'Serial Number',
            'items.*.mac_address' => 'MAC Address',
            'items.*.brand_id' => 'Brand',
        ];
    }
}
