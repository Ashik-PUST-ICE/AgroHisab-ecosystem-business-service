<?php

namespace App\Services\Business\Supplier;

use App\Models\Business\Supplier;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Supplier::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('market', 'like', "%{$search}%"))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when($filters['district'] ?? null, fn($q, $district) => $q->where('district', $district))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Supplier
    {
        $data['status'] = $data['status'] ?? STATUS_ACTIVE;
        $data['created_by'] = authId();

        return Supplier::create($data);
    }

    public function show(Supplier $supplier): Supplier
    {
        return $supplier;
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->fresh();
    }

    public function destroy(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
