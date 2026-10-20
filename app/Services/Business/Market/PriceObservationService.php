<?php

namespace App\Services\Business\Market;

use App\Models\Business\Market;
use App\Models\Business\PriceObservation;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PriceObservationService
{
    /**
     * List price observations for a market.
     * Filters: product_id, from_date, to_date, per_page
     */
    public function list(Market $market, array $filters = []): LengthAwarePaginator
    {
        $query = PriceObservation::where('market_id', $market->id)
            ->with(['product:id,name', 'unit:id,name,symbol']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('observed_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('observed_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        $perPage = (int) ($filters['per_page'] ?? 30);
        return $query->orderByDesc('observed_date')->paginate($perPage);
    }

    public function store(Market $market, array $data): PriceObservation
    {
        return PriceObservation::create(array_merge($data, [
            'market_id'     => $market->id,
            'observed_date' => Carbon::parse($data['observed_date'])->toDateString(),
            'created_by'    => authId(),
        ]));
    }

    public function destroy(PriceObservation $observation): void
    {
        $observation->delete();
    }
}