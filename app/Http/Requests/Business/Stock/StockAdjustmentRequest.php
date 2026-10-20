<?php

namespace App\Http\Requests\Business\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'      => 'required|integer|exists:products,id',
            'loss_type'       => 'required|integer|in:1,2,3,4,5,6',
            'quantity'        => 'required|numeric|min:0.0001',
            'unit_id'         => 'required|integer|exists:units,id',
            'adjustment_date' => 'required|date',
            'market_id'       => 'nullable|integer|exists:markets,id',
            'reason'          => 'nullable|string|max:255',
            'notes'           => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'      => 'Product is required.',
            'loss_type.required'       => 'Loss/Adjustment type is required (1: Spoilage, 2: Weight Loss, 3: Damage, 4: Sorting Waste, 5: Shortage, 6: Count Reconciliation).',
            'quantity.required'        => 'Quantity is required.',
            'quantity.min'             => 'Quantity must be greater than 0.',
            'unit_id.required'         => 'Unit is required.',
            'adjustment_date.required' => 'Adjustment date is required.',
        ];
    }
}