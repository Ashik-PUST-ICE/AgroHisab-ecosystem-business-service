<?php

namespace App\Http\Resources\Modules\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'branch' => $this->whenLoaded('branch'),
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'account' => $this->whenLoaded('account'),
            'description' => $this->description,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date?->format('Y-m-d H:i:s'),
            'created_by' => $this->created_by,
            // 'transactionable_type' => $this->transactionable_type,
            // 'transactionable_id' => $this->transactionable_id,
            'transactionable' => $this->whenLoaded('transactionable'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
