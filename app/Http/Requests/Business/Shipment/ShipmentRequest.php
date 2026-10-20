<?php

namespace App\Http\Requests\Business\Shipment;

use App\Models\Business\Shipment;
use Illuminate\Foundation\Http\FormRequest;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('shipment') instanceof Shipment;

        return [
            'aratdar_id'             => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:aratdars,id'],
            'transporter_id'         => ['nullable', 'integer', 'exists:transporters,id'],
            'market_id'              => ['nullable', 'integer', 'exists:markets,id'],
            'expected_arrival_date'  => ['nullable', 'date'],
            'vehicle_number'         => ['nullable', 'string', 'max:50'],
            'freight_amount'         => ['nullable', 'numeric', 'min:0'],
            'freight_paid_by'        => ['nullable', 'in:owner,aratdar,shared'],
            'notes'                  => ['nullable', 'string'],
            'expenses'               => ['nullable', 'array'],
            'expenses.*.type'        => ['nullable', 'integer', 'in:1,2,3,4,5,6,7,8,9,10'],
            'expenses.*.head'        => ['nullable', 'string', 'max:150'],
            'expenses.*.amount'      => ['required_with:expenses', 'numeric', 'min:0'],
            'expenses.*.paid_by'     => ['nullable', 'string', 'in:owner,aratdar,shared'],
            'expenses.*.notes'       => ['nullable', 'string', 'max:255'],
            'items'                  => [$isUpdate ? 'sometimes' : 'required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required_with:items', 'integer', 'exists:purchase_items,id'],
            'items.*.bag_count'      => ['nullable', 'integer', 'min:0'],
            'items.*.dispatched_weight' => ['required_with:items', 'numeric', 'min:0.0001'],
            'items.*.notes'          => ['nullable', 'string', 'max:255'],
        ];
    }
}