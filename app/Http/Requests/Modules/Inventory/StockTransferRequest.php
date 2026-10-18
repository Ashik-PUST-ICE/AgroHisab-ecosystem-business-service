<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
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
            'transfer_product_id' => 'required|exists:products,id|different:stock_product_id',
            's_mtr' => 'nullable|string|max:255',
            'e_mtr' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:1',
            'unit_id' => 'nullable|exists:units,id',
            'product_code' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'unit_price' => 'nullable|numeric|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
