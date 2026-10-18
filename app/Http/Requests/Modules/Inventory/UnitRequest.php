<?php

namespace App\Http\Requests\Modules\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UnitRequest extends FormRequest
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
        // dd($this->route('unit'), $this->unit);
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:units,slug,'.$this->route('unit'),
        ];
    }

    protected function prepareForValidation()
    {

        $this->merge([
            'slug' => Str::slug($this->name),
        ]);
    }
}
