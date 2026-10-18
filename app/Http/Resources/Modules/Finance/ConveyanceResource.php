<?php

namespace App\Http\Resources\Modules\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConveyanceResource extends JsonResource
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
            'purpose' => $this->purpose,
            'user_id' => $this->user_id,
            'voucher' => $this->voucher,
            'account' => $this->whenLoaded('account'),
            'account_id' => $this->account_id,
            'amount' => $this->amount,
            'biling_date' => $this->biling_date?->format('Y-m-d'),
            'description' => $this->description,
            'finance_category_id' => $this->finance_category_id,
            'created_by' => $this->created_by,
            'transactions' => $this->whenLoaded('transactions'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
