<?php

namespace App\Http\Requests\Modules\Inventory;

use App\Enums\Commons\EntityType\EntityTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StockOutRequest extends FormRequest
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
        $assignableType = $this->input('assignable_type', null);
        $isClient = $assignableType === EntityTypeEnum::CLIENT->value;

        return [
            'stock_category_id' => 'required|exists:stock_categories,id',
            'stock_product_id' => 'nullable|exists:products,id',
            'stock_history_id' => 'nullable|exists:stock_histories,id',
            'network_id' => $isClient ? 'required|integer' : 'nullable|integer',
            's_mtr' => 'nullable|numeric',
            'e_mtr' => 'nullable|numeric|gte:s_mtr',
            'quantity' => 'required|numeric',
            'unit_id' => 'required|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'serial_no' => 'nullable|string',
            'mac_address' => 'nullable|string',
            'product_code' => 'nullable|string',
            'assignable_type' => 'nullable|string|in:'.implode(',', array_column(EntityTypeEnum::cases(), 'value')),
            'assignable_id' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'requisition_id' => 'nullable|integer|exists:requisitions,id',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'warranty' => 'nullable|string',
            'unit_price' => 'nullable|numeric',
            'color' => 'nullable|string',
            'is_returned' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
