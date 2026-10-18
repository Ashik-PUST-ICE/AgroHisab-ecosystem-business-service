<?php

namespace App\Services\Business\Beat;

use App\Models\Business\Beat;
use App\Models\Business\BeatOutlet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class BeatService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Beat::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function today(array $filters): LengthAwarePaginator
    {
        $userId = authId();

        return Beat::query()
            ->whereDate('date', now()->toDateString())
            ->when($userId, fn($q) => $q->where('assigned_user_id', $userId))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Beat
    {
        return Beat::create($data);
    }

    public function show(Beat $beat): Beat
    {
        return $beat->load('outlets', 'assignedUser');
    }

    public function update(Beat $beat, array $data): Beat
    {
        $beat->update($data);

        return $beat;
    }

    public function destroy(Beat $beat): void
    {
        $beat->delete();
    }

    public function markOutletVisited(int $beatId, int $outletId, ?int $visitId = null): void
    {
        $beatOutlet = BeatOutlet::where('beat_id', $beatId)
            ->where('outlet_id', $outletId)
            ->first();

        if ($beatOutlet) {
            $beatOutlet->update([
                'status' => true,
                'visit_id' => $visitId,
                'visited_at' => now(),
            ]);
        }
    }
}
