<?php

namespace App\Http\Requests\Business\Visit;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'string', 'max:255'],
            'longitude' => ['required', 'string', 'max:255'],
        ];
    }
}
