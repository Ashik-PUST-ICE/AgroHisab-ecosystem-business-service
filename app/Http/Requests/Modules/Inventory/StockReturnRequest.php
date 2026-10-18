<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockReturnRequest extends FormRequest
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
            'return_product_id' => 'nullable|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_id' => 'nullable|exists:units,id',
            'product_code' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'returnable_type' => 'nullable|string',
            'returnable_id' => 'required|integer',
            'return_type' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
            'return_date' => 'nullable|date',
            's_mtr' => 'nullable|numeric|required_with:e_mtr',
            'e_mtr' => 'nullable|numeric|gte:s_mtr',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
