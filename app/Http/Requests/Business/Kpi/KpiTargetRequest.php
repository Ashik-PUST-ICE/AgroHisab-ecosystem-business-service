<?php

namespace App\Http\Requests\Business\Kpi;

use Illuminate\Foundation\Http\FormRequest;

class KpiTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'title' => ['nullable', 'string', 'max:255'],
            'period_type' => ['nullable', 'string', 'in:daily,weekly,monthly'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'visit_target' => ['nullable', 'integer', 'min:0'],
            'order_amount_target' => ['nullable', 'numeric', 'min:0'],
            'order_count_target' => ['nullable', 'integer', 'min:0'],
            'coverage_target_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
