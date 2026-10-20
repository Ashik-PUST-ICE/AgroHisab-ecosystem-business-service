<?php

namespace App\Http\Resources\Business\Diary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'event_date'   => $this->event_date?->toDateString(),
            'title'        => $this->title,
            'notes'        => $this->notes,
            'event_type'   => $this->event_type,
            'related_type' => $this->related_type,
            'related_id'   => $this->related_id,
            'created_by'   => $this->created_by,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}