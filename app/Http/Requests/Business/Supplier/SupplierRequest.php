<?php

namespace App\Http\Requests\Business\Supplier;

use App\Models\Business\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('supplier') instanceof Supplier;

        return [
            'name'     => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:500'],
            'market'   => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:100'],
            'notes'    => ['nullable', 'string'],
            'status'   => ['nullable', 'integer', Rule::in(ACTIVE_INACTIVE_STATUSES)],
        ];
    }
}
