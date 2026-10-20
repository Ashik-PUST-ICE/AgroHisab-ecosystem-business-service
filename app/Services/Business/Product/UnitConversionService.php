<?php

namespace App\Services\Business\Product;

use App\Models\Business\UnitConversion;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitConversionService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return UnitConversion::query()
            ->with(['fromUnit', 'toUnit'])
            ->when($filters['from_unit_id'] ?? null, fn($q, $id) => $q->where('from_unit_id', $id))
            ->when($filters['to_unit_id'] ?? null, fn($q, $id) => $q->where('to_unit_id', $id))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): UnitConversion
    {
        if (function_exists('authId') && authId()) {
            $data['created_by'] = authId();
        }
        $conversion = UnitConversion::create($data);
        return $conversion->load(['fromUnit', 'toUnit']);
    }

    public function show(UnitConversion $conversion): UnitConversion
    {
        return $conversion->load(['fromUnit', 'toUnit']);
    }

    public function update(UnitConversion $conversion, array $data): UnitConversion
    {
        $conversion->update($data);
        return $conversion->load(['fromUnit', 'toUnit']);
    }

    public function destroy(UnitConversion $conversion): void
    {
        $conversion->delete();
    }

    public function convert(int $fromUnitId, int $toUnitId, float $quantity): ?float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        $direct = UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->where('status', STATUS_ACTIVE)
            ->first();

        if ($direct) {
            return $quantity * (float) $direct->factor;
        }

        $inverse = UnitConversion::where('from_unit_id', $toUnitId)
            ->where('to_unit_id', $fromUnitId)
            ->where('status', STATUS_ACTIVE)
            ->first();

        if ($inverse && (float) $inverse->factor > 0) {
            return $quantity / (float) $inverse->factor;
        }

        return null;
    }
}