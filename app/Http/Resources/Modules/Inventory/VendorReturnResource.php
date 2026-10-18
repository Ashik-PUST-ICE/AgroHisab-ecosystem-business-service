<?php

namespace App\Http\Resources\Modules\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorReturnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_category_id' => $this->stock_category_id,
            'stock_product_id' => $this->stock_product_id,
            'quantity' => $this->quantity,
            'brand_id' => $this->brand_id,
            'unit_id' => $this->unit_id,
            's_mtr' => $this->s_mtr,
            'e_mtr' => $this->e_mtr,
            'vendor_id' => $this->vendor_id,
            'return_type' => $this->return_type,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'replace_product_id' => $this->replace_product_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'stock_category' => $this->whenLoaded('stockCategory'),
            'stock_product' => $this->whenLoaded('stockProduct'),
            'brand' => $this->whenLoaded('brand'),
            'unit' => $this->whenLoaded('unit'),
            'vendor' => $this->whenLoaded('vendor'),
            'account' => $this->whenLoaded('account'),
            'replace_product' => $this->whenLoaded('replaceProduct'),

            'transactions' => $this->whenLoaded('transactions', function () {
                return $this->transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'branch_id' => $transaction->branch_id,
                        'transaction_id' => $transaction->transaction_id,
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'description' => $transaction->description,
                        'transaction_date' => $transaction->transaction_date,
                    ];
                });
            }),
        ];
    }
}
