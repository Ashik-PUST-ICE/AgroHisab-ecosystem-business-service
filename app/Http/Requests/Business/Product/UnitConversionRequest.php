<?php

namespace App\Http\Requests\Business\Product;

use App\Models\Business\UnitConversion;
use Illuminate\Foundation\Http\FormRequest;

class UnitConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('conversion') instanceof UnitConversion;

        return [
            'from_unit_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:units,id'],
            'to_unit_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:units,id', 'different:from_unit_id'],
            'factor' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
