<?php

namespace App\Http\Requests\Business\Market;

use Illuminate\Foundation\Http\FormRequest;

class PriceObservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'    => ['required', 'integer', 'exists:products,id'],
            'unit_id'       => ['nullable', 'integer', 'exists:units,id'],
            'observed_date' => ['required', 'date'],
            'min_price'     => ['nullable', 'numeric', 'gte:0'],
            'max_price'     => ['nullable', 'numeric', 'gte:min_price'],
            'avg_price'     => ['nullable', 'numeric', 'gte:0'],
            'source'        => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }
}