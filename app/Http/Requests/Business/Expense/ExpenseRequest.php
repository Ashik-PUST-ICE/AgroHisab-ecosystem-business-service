<?php

namespace App\Http\Requests\Business\Expense;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'purchase_id'         => ['nullable', 'integer', 'exists:purchases,id'],
            'shipment_id'         => ['nullable', 'integer', 'exists:shipments,id'],
            'account_id'          => ['nullable', 'integer', 'exists:accounts,id'],
            'title'               => ['required', 'string', 'max:255'],
            'amount'              => ['required', 'numeric', 'gt:0'],
            'expense_date'        => ['required', 'date'],
            'paid_by'             => ['nullable', 'string', 'in:owner,aratdar,shared'],
            'payment_status'      => ['nullable', 'integer', 'in:0,1,2'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ];
    }
}