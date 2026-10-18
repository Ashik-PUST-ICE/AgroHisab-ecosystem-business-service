<?php

namespace App\Http\Resources\Modules\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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
            'operational_branch_id' => $this->operational_branch_id,
            'branch_id' => $this->branch_id,
            'title' => $this->title,
            'account_holder_name' => $this->account_holder_name,
            'account_no' => $this->account_no,
            'bank_name' => $this->bank_name,
            'branch_name' => $this->branch_name,
            'routing_no' => $this->routing_no,
            'opening_balance' => $this->opening_balance,
            'account_type_id' => $this->account_type_id,
            'central_account_id' => $this->central_account_id,
            'central_account' => $this->whenLoaded('centralAccount'),
            'transactions' => $this->whenLoaded('transactions'),
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
