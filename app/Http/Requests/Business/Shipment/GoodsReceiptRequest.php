<?php

namespace App\Http\Requests\Business\Shipment;

use Illuminate\Foundation\Http\FormRequest;

class GoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'received_date'               => ['required', 'date'],
            'notes'                       => ['nullable', 'string'],
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.shipment_item_id'    => ['required', 'integer', 'exists:shipment_items,id'],
            'items.*.received_weight'     => ['required', 'numeric', 'min:0'],
            'items.*.shortage_reason'     => ['nullable', 'string', 'max:255'],
        ];
    }
}