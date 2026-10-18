<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class VendorReturnRequest extends FormRequest
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
            'stock_category_id' => 'nullable|exists:stock_categories,id',
            'stock_product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            's_mtr' => 'nullable|numeric|min:0',
            'e_mtr' => 'nullable|numeric|min:0',
            'vendor_id' => 'required|exists:vendors,id',
            'return_type' => 'required|in:Amount,Product',
            'amount' => 'required_if:return_type,Amount|numeric|min:0',
            'account_id' => 'required_if:return_type,Amount|exists:accounts,id',
            'replace_product_id' => 'required_if:return_type,Product|exists:products,id|different:stock_product_id',
            'fiber_code' => 'nullable|string|max:255',
            'serial_no' => 'nullable|array',
            'serial_no.*' => 'string|max:255',
            'unit' => 'nullable|string|max:255',
            'remark' => 'nullable|string|max:1000',
            'date' => 'nullable|date',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
