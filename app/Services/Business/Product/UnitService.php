<?php

namespace App\Services\Business\Product;

use App\Models\Business\Unit;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Unit::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Unit
    {
        if (function_exists('authId') && authId()) {
            $data['created_by'] = authId();
        }
        return Unit::create($data);
    }

    public function show(Unit $unit): Unit
    {
        return $unit;
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit;
    }

    public function destroy(Unit $unit): void
    {
        $unit->delete();
    }
}