<?php

namespace App\Http\Requests\Business\Order;

use App\Models\Business\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('orderItem') instanceof \App\Models\Business\OrderItem;

        return [
            'product_id' => [$isUpdate ? 'sometimes' : 'required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'total_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
