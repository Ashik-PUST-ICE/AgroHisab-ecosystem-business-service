<?php

namespace App\Http\Requests\Business\Product;

use App\Models\Business\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('product') instanceof Product;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'variety' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'default_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}