<?php

namespace App\Services\Business\Outlet;

use App\Models\Business\Outlet;
use Illuminate\Pagination\LengthAwarePaginator;

class OutletService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Outlet::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Outlet
    {
        return Outlet::create($data);
    }

    public function show(Outlet $outlet): Outlet
    {
        return $outlet;
    }

    public function update(Outlet $outlet, array $data): Outlet
    {
        $outlet->update($data);

        return $outlet;
    }

    public function destroy(Outlet $outlet): void
    {
        $outlet->delete();
    }

    public function regenerateQr(Outlet $outlet): Outlet
    {
        $outlet->update([
            'qr_token' => str()->random(32),
            'qr_status' => 'active',
            'qr_generated_at' => now(),
            'qr_deactivated_at' => null,
        ]);

        return $outlet;
    }

    public function deactivateQr(Outlet $outlet): Outlet
    {
        $outlet->update([
            'qr_status' => 'inactive',
            'qr_deactivated_at' => now(),
        ]);

        return $outlet;
    }

    public function downloadQr(Outlet $outlet): array
    {
        if (! $outlet->qr_token) {
            $outlet->update([
                'qr_token' => str()->random(32),
                'qr_generated_at' => now(),
            ]);
        }

        return [
            'token' => $outlet->qr_token,
            'url' => urlencode(route('outlets.verify-qr', ['qr_token' => $outlet->qr_token])),
        ];
    }
}
