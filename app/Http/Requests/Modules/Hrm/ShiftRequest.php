<?php

namespace App\Http\Requests\Modules\Hrm;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'work_schedule_id' => ['required', 'exists:work_schedules,id'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
            'break_minutes' => ['nullable', 'integer', 'min:0'],
            'is_night_shift' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:7'],
            'effective_at' => ['nullable', 'date'],
            'flexible_time' => ['nullable', 'boolean'],
        ];
    }
}
