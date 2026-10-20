<?php

namespace App\Http\Requests\Business\Market;

use App\Models\Business\Market;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('market') instanceof Market;

        return [
            'name'          => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'district'      => ['nullable', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'type'          => ['nullable', Rule::in([MARKET_SOURCE, MARKET_DESTINATION, MARKET_BOTH])],
            'contact_notes' => ['nullable', 'string'],
            'status'        => ['nullable', Rule::in([STATUS_INACTIVE, STATUS_ACTIVE])],
        ];
    }
}
