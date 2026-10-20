<?php

namespace App\Http\Requests\Business\Purchase;

use App\Models\Business\Purchase;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('purchase') instanceof Purchase;

        return [
            'purchase_date'        => [$isUpdate ? 'sometimes' : 'required', 'date'],
            'supplier_id'          => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:suppliers,id'],
            'market_id'            => ['nullable', 'integer', 'exists:markets,id'],
            'discount_amount'      => ['nullable', 'numeric', 'min:0'],
            'paid_amount'          => ['nullable', 'numeric', 'min:0'],
            'status'               => ['nullable', 'in:' . PURCHASE_STATUS_DRAFT . ',' . PURCHASE_STATUS_CONFIRMED . ',' . PURCHASE_STATUS_RECEIVED . ',' . PURCHASE_STATUS_CANCELLED],
            'notes'                => ['nullable', 'string'],
            'items'                => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'items.*.product_id'   => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.unit_id'      => ['nullable', 'integer', 'exists:units,id'],
            'items.*.bag_count'    => ['nullable', 'integer', 'min:0'],
            'items.*.gross_weight' => ['nullable', 'numeric', 'min:0'],
            'items.*.tare_weight'  => ['nullable', 'numeric', 'min:0'],
            'items.*.net_weight'   => ['required_with:items', 'numeric', 'min:0.0001'],
            'items.*.unit_rate'    => ['required_with:items', 'numeric', 'min:0'],
            'items.*.notes'        => ['nullable', 'string', 'max:255'],
        ];
    }
}