<?php

namespace App\Services\Business\Transporter;

use App\Models\Business\Transporter;
use Illuminate\Pagination\LengthAwarePaginator;

class TransporterService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Transporter::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('truck_number', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%")
                    ->orWhere('driver_phone', 'like', "%{$search}%");
            }))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Transporter
    {
        $data['created_by'] = authId();

        return Transporter::create($data);
    }

    public function show(Transporter $transporter): Transporter
    {
        return $transporter;
    }

    public function update(Transporter $transporter, array $data): Transporter
    {
        $transporter->update($data);

        return $transporter->fresh();
    }

    public function destroy(Transporter $transporter): void
    {
        $transporter->delete();
    }
}