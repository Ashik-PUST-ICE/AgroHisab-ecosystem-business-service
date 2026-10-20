<?php

namespace App\Http\Requests\Business\Shipment;

use Illuminate\Foundation\Http\FormRequest;

class DispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dispatch_date'  => ['required', 'date'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}