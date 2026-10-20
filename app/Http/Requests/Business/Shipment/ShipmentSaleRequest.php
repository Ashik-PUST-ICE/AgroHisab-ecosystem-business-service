<?php

namespace App\Http\Requests\Business\Shipment;

use Illuminate\Foundation\Http\FormRequest;

class ShipmentSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipment_item_id'  => ['nullable', 'integer', 'exists:shipment_items,id'],
            'product_id'        => ['required', 'integer', 'exists:products,id'],
            'unit_id'           => ['nullable', 'integer', 'exists:units,id'],
            'sale_date'         => ['required', 'date'],
            'quantity'          => ['required', 'numeric', 'gt:0'],
            'unit_rate'         => ['required', 'numeric', 'gte:0'],
            'discount_amount'   => ['nullable', 'numeric', 'gte:0'],
            'commission_type'   => ['nullable', 'in:1,2'],
            'commission_rate'   => ['nullable', 'numeric', 'gte:0'],
            'buyer_name'        => ['nullable', 'string', 'max:255'],
            'buyer_phone'       => ['nullable', 'string', 'max:50'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }
}