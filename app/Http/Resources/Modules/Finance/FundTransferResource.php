<?php

namespace App\Http\Resources\Modules\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundTransferResource extends JsonResource
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
            'account_from' => $this->whenLoaded('accountFrom'),
            'account_to' => $this->whenLoaded('accountTo'),
            'transfer_date' => $this->transfer_date?->format('Y-m-d H:i:s'),
            'description' => $this->description,
            'amount' => $this->amount,
            'created_by' => $this->created_by,
            'transactions' => $this->whenLoaded('transactions'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
