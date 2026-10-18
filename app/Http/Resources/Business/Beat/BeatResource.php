<?php

namespace App\Http\Resources\Business\Beat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'assigned_user_id' => $this->assigned_user_id,
            'date' => $this->date?->toDateString(),
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
