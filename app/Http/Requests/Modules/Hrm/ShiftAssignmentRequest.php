<?php

namespace App\Http\Requests\Modules\Hrm;

use Illuminate\Foundation\Http\FormRequest;

class ShiftAssignmentRequest extends FormRequest
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
            'user_id' => ['required', 'integer'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'effective_from' => ['nullable', 'date', 'before_or_equal:effective_to'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],

        ];
    }
}
