<?php

namespace App\Services\Business\Competitor;

use App\Models\Business\Competitor;
use Illuminate\Pagination\LengthAwarePaginator;

class CompetitorService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Competitor::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Competitor
    {
        return Competitor::create($data);
    }

    public function show(Competitor $competitor): Competitor
    {
        return $competitor;
    }

    public function update(Competitor $competitor, array $data): Competitor
    {
        $competitor->update($data);

        return $competitor;
    }

    public function destroy(Competitor $competitor): void
    {
        $competitor->delete();
    }
}
