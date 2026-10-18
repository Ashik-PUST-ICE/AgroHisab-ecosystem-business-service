<?php

namespace App\Services\Business\Outlet;

use App\Models\Business\OutletAssignment;
use Illuminate\Pagination\LengthAwarePaginator;

class OutletAssignmentService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return OutletAssignment::query()
            ->when($filters['outlet_id'] ?? null, fn($q, $id) => $q->where('outlet_id', $id))
            ->when($filters['user_id'] ?? null, fn($q, $id) => $q->where('user_id', $id))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): OutletAssignment
    {
        return OutletAssignment::create($data);
    }

    public function show(OutletAssignment $assignment): OutletAssignment
    {
        return $assignment;
    }

    public function update(OutletAssignment $assignment, array $data): OutletAssignment
    {
        $assignment->update($data);

        return $assignment;
    }

    public function destroy(OutletAssignment $assignment): void
    {
        $assignment->delete();
    }
}
