<?php

namespace App\Http\Requests\Business\Transporter;

use App\Models\Business\Transporter;
use Illuminate\Foundation\Http\FormRequest;

class TransporterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('transporter') instanceof Transporter;

        return [
            'name'         => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'truck_number' => ['nullable', 'string', 'max:50'],
            'driver_name'  => ['nullable', 'string', 'max:255'],
            'driver_phone' => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'string', 'max:500'],
            'notes'        => ['nullable', 'string'],
            'status'       => ['nullable', 'in:' . STATUS_ACTIVE . ',' . STATUS_INACTIVE],
        ];
    }
}