<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BandwidthPurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'link'             => $this->link,
            'total_amount'     => $this->total_amount,
            'discount_type'    => $this->discount_type,
            'discount_value'   => $this->discount_value,
            'additional_amount' => $this->additional_amount,
            'paid_amount'      => $this->paid_amount,
            'due_amount'       => $this->due_amount,
            'purchase_date'    => $this->purchase_date->format('Y-m-d'),
            'invoice'          => $this->invoice,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'account'          => $this->whenLoaded('account', function () {
                return [
                    'id'   => $this->account->id,
                    'name' => $this->account->account_holder_name,
                     'transactions' => $this->account->transactions->map(function ($trx) {
                        return [

                            'transaction_id' => $trx->transaction_id,
                            'amount' => $trx->amount,
                            'transaction_date' => $trx->transaction_date,
                        ];
                    }),

                ];
            }),

            'vendor'           => $this->whenLoaded('vendor', function () {
                return [
                    'id'         => $this->vendor->id,
                    'first_name' => $this->vendor->first_name,
                    'last_name'  => $this->vendor->last_name,
                ];
            }),


            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id'           => $item->id,
                        'quantity'     => $item->quantity,
                        'amount'       => $item->amount,
                        'total_amount' => $item->total_amount,
                        'product'      => $item->relationLoaded('product') ? [
                            'id'    => $item->product->id,
                            'name'  => $item->product->name,
                        ] : null,
                        'unit'         => $item->relationLoaded('unit') ? [
                            'id'   => $item->unit->id,
                            'name' => $item->unit->name,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
