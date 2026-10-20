<?php

namespace App\Http\Resources\Business\Shipment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'settlement_number'     => $this->settlement_number,
            'shipment_id'           => $this->shipment_id,
            'shipment_number'       => $this->shipment?->shipment_number,
            'aratdar_id'            => $this->aratdar_id,
            'aratdar_name'          => $this->aratdar?->name,
            'aratdar_business'      => $this->aratdar?->business_name,
            'settlement_date'       => $this->settlement_date?->format('Y-m-d'),
            'gross_sales'           => (float) $this->gross_sales,
            'sale_discounts'        => (float) $this->sale_discounts,
            'net_sales'             => (float) $this->net_sales,
            'commission_amount'     => (float) $this->commission_amount,
            'owner_freight'         => (float) $this->owner_freight,
            'aratdar_freight'       => (float) $this->aratdar_freight,
            'other_deductions'      => (float) $this->other_deductions,
            'expected_net_proceeds' => (float) $this->expected_net_proceeds,
            'remitted_amount'       => (float) $this->remitted_amount,
            'outstanding_balance'   => (float) $this->outstanding_balance,
            'allocated_cogs'        => (float) $this->allocated_cogs,
            'realized_profit'       => (float) $this->realized_profit,
            'status'                => $this->status,
            'notes'                 => $this->notes,
            'approved_at'           => $this->approved_at?->toIso8601String(),
            'created_at'            => $this->created_at?->toIso8601String(),
        ];
    }
}