<?php

namespace App\Http\Requests\Modules\Hrm\Holidays;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'holiday_group_id' => ['required', 'exists:holiday_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'scope_type' => ['required', 'string', 'max:255', 'in:company,office,department,team'],
            'scope_id' => ['required', 'integer'],

        ];
    }

    protected function prepareForValidation()
    {
        if (empty($this->end_date)) {
            $this->merge([
                'end_date' => $this->start_date,
            ]);
        }
    }
}
