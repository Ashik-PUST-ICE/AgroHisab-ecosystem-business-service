<?php

namespace App\Http\Requests\Business\Outlet;

use Illuminate\Foundation\Http\FormRequest;

class VerifyQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_token' => ['required', 'string', 'exists:outlets,qr_token'],
            'latitude' => ['required', 'string', 'max:255'],
            'longitude' => ['required', 'string', 'max:255'],
        ];
    }
}
