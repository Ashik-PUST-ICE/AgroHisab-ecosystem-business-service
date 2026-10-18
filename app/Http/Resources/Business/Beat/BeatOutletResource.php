<?php

namespace App\Http\Resources\Business\Beat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeatOutletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'beat_id' => $this->beat_id,
            'outlet_id' => $this->outlet_id,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'visited_at' => $this->visited_at,
            'outlet' => $this->whenLoaded('outlet', fn() => [
                'id' => $this->outlet->id,
                'name' => $this->outlet->name,
                'address' => $this->outlet->address,
                'latitude' => $this->outlet->latitude,
                'longitude' => $this->outlet->longitude,
                'geofence_radius' => $this->outlet->geofence_radius,
            ]),
        ];
    }
}
