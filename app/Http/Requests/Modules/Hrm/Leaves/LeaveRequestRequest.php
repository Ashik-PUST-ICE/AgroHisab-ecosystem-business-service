<?php

namespace App\Http\Requests\Modules\Hrm\Leaves;

use App\Models\FiscalYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class LeaveRequestRequest extends FormRequest
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
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'fiscal_year_id' => ['nullable', 'exists:fiscal_years,id'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['nullable', 'numeric', 'min:0.5'],
            'hours' => ['nullable', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'applied_to' => ['required', 'integer'],
        ];
    }

    protected function prepareForValidation()
    {
        if (! $this->start_date || ! $this->end_date) {
            return;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        // One-liner working days calculation
        $workingDays = collect(range(0, $start->diffInDays($end)))
            ->filter(fn ($day) => ! in_array($start->copy()->addDays($day)->dayOfWeek, [0, 6]))
            ->count();

        $this->merge([
            'days' => $workingDays,
            'hours' => $workingDays * 8,
            'fiscal_year_id' => FiscalYear::whereDate('start_date', '<=', $start)
                ->whereDate('end_date', '>=', $start)
                ->value('id'),
        ]);
    }
}
