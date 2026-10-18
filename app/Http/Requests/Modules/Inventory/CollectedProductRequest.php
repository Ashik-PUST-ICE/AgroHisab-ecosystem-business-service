<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class CollectedProductRequest extends FormRequest
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
            'stock_category_id' => ['nullable', 'exists:stock_categories,id'],
            'stock_product_id' => ['nullable', 'exists:products,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            's_mtr' => ['required', 'string'],
            'e_mtr' => ['required', 'string'],
            'product_code' => ['nullable', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'collected_date' => ['required', 'date'],
            'warranty' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
            'collectable_type' => ['nullable', 'string'],
            'collectable_id' => ['required', 'integer'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
