<?php

namespace App\Http\Requests\Business\ExpenseCategory;

use App\Models\Business\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('expense_category') instanceof ExpenseCategory;
        $id = $this->route('expense_category')?->id;

        return [
            'name'        => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50', 'unique:expense_categories,code,' . ($id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'in:0,1'],
        ];
    }
}
