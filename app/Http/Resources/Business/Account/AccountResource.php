<?php

namespace App\Http\Resources\Business\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'type'            => (int) $this->type,
            'type_label'      => account_type_label($this->type),
            'account_number'  => $this->account_number,
            'bank_name'       => $this->bank_name,
            'branch'          => $this->branch,
            'opening_balance' => (float) $this->opening_balance,
            'notes'           => $this->notes,
            'status'          => (int) $this->status,
            'status_label'    => status_label($this->status),
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}