<?php

namespace App\Services\Business\Market;

use App\Models\Business\Market;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Market::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->when(isset($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when($filters['district'] ?? null, fn($q, $district) => $q->where('district', $district))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Market
    {
        $data['created_by'] = authId();

        return Market::create($data);
    }

    public function show(Market $market): Market
    {
        return $market;
    }

    public function update(Market $market, array $data): Market
    {
        $market->update($data);

        return $market->fresh();
    }

    public function destroy(Market $market): void
    {
        $market->delete();
    }
}