<?php

namespace App\Services\Business\Aratdar;

use App\Models\Business\Aratdar;
use Illuminate\Pagination\LengthAwarePaginator;

class AratdarService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Aratdar::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('business_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('market', 'like', "%{$search}%"))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when($filters['district'] ?? null, fn($q, $district) => $q->where('district', $district))
            ->when($filters['commission_type'] ?? null, fn($q, $type) => $q->where('commission_type', $type))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Aratdar
    {
        $data['created_by'] = authId();

        return Aratdar::create($data);
    }

    public function show(Aratdar $aratdar): Aratdar
    {
        return $aratdar;
    }

    public function update(Aratdar $aratdar, array $data): Aratdar
    {
        $aratdar->update($data);

        return $aratdar->fresh();
    }

    public function destroy(Aratdar $aratdar): void
    {
        $aratdar->delete();
    }
}
