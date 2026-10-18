<?php

namespace App\Http\Requests\Business\Competitor;

use App\Models\Business\Competitor;
use Illuminate\Foundation\Http\FormRequest;

class CompetitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('competitor') instanceof Competitor;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
