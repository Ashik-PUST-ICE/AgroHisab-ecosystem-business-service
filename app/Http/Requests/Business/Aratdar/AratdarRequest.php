<?php

namespace App\Http\Requests\Business\Aratdar;

use App\Models\Business\Aratdar;
use Illuminate\Foundation\Http\FormRequest;

class AratdarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('aratdar') instanceof Aratdar;

        return [
            'name'             => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'business_name'    => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'address'          => ['nullable', 'string', 'max:500'],
            'market'           => ['nullable', 'string', 'max:255'],
            'district'         => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string'],
            'agreement_start'  => ['nullable', 'date'],
            'agreement_end'    => ['nullable', 'date', 'after_or_equal:agreement_start'],
            'commission_type'  => ['nullable', 'in:' . COMMISSION_TYPE_PERCENTAGE . ',' . COMMISSION_TYPE_FIXED],
            'commission_value' => ['nullable', 'numeric', 'min:0'],
            'commission_base'  => ['nullable', 'in:gross_sales,net_sales'],
            'status'           => ['nullable', 'in:' . STATUS_ACTIVE . ',' . STATUS_INACTIVE],
        ];
    }
}
