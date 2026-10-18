<?php

namespace App\Http\Resources\Business\Visit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitCompetitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'competitor_id' => $this->competitor_id,
            'notes' => $this->notes,
            'competitor' => $this->whenLoaded('competitor', fn() => [
                'id' => $this->competitor->id,
                'name' => $this->competitor->name,
            ]),
        ];
    }
}
