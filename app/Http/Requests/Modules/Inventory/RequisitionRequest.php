<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionRequest extends FormRequest
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
            'stock_category_id' => 'required|exists:stock_categories,id',
            'product_id' => 'required|exists:products,id',
            'purpose' => 'required|string',
            'vendor_id' => 'required|exists:vendors,id',
            'brand_id' => 'required|exists:brands,id',
            'product_code' => 'nullable|string',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'created_by' => 'required|integer',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
