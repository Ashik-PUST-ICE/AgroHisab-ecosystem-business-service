<?php

namespace App\Http\Requests\Business\Product;

use App\Models\Business\Unit;
use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('unit') instanceof Unit;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
