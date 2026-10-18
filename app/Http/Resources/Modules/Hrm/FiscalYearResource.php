<?php

namespace App\Http\Resources\Modules\Hrm;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiscalYearResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status?->boolValue(),
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A'),
        ];
    }
}
