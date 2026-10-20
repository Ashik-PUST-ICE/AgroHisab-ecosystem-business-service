<?php

namespace App\Http\Requests\Business\Diary;

use Illuminate\Foundation\Http\FormRequest;

class DiaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_date'   => ['required', 'date'],
            'title'        => ['required', 'string', 'max:255'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'event_type'   => ['nullable', 'string', 'in:general,purchase,shipment,sale,payment,expense,settlement,reminder,other'],
            'related_type' => ['nullable', 'string', 'max:100'],
            'related_id'   => ['nullable', 'integer'],
        ];
    }
}