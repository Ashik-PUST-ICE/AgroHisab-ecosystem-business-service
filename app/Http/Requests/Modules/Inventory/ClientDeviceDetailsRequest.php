<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ClientDeviceDetailsRequest extends FormRequest
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
            'user_id' => 'required|integer',
            'network_id' => 'required|integer',
            'model' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => authId(),
        ]);
    }
}
