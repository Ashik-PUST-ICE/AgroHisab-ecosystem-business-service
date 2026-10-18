<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockCustomerOnuRequest extends FormRequest
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
            'user_id' => 'nullable|integer',
            'network_id' => 'required|integer',
            'provided_by' => 'required|string|in:customer,stock',
            'brand' => 'nullable|string|max:255',
            'serial_no' => 'required|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
        ];
    }
}
