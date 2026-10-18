<?php

namespace App\Services\Business\Map;

use App\Models\Business\Outlet;

class MapService
{
    public function outlets(array $filters)
    {
        $lat = $filters['lat'] ?? null;
        $lng = $filters['lng'] ?? null;
        $radius = (int) ($filters['radius'] ?? 50000);

        return Outlet::query()
            ->when($lat !== null && $lng !== null, function ($q) use ($lat, $lng, $radius) {
                $q->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
                    ->having('distance', '<=', $radius / 1000)
                    ->orderBy('distance');
            })
            ->when(! empty($filters['beat_id']), fn($q) => $q->whereHas('beatOutlets', fn($bq) => $bq->where('beat_id', $filters['beat_id'])))
            ->when(! empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->get();
    }

    public function nearby(array $filters)
    {
        $lat = (float) $filters['lat'];
        $lng = (float) $filters['lng'];
        $radius = (int) ($filters['radius'] ?? 50000);

        return Outlet::query()
            ->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius / 1000)
            ->orderBy('distance')
            ->get();
    }
}
