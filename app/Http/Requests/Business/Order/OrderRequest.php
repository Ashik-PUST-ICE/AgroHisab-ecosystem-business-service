<?php

namespace App\Http\Requests\Business\Order;

use App\Models\Business\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('order') instanceof Order;

        return [
            'outlet_id' => [$isUpdate ? 'sometimes' : 'required', 'exists:outlets,id'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'ordered_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
        ];
    }
}
