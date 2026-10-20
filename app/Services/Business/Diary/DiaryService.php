<?php

namespace App\Services\Business\Diary;

use App\Models\Business\BusinessDiary;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DiaryService
{
    /**
     * Paginated timeline — all diary entries.
     * Spec §3.13: "Timeline of business events with date/time and notes"
     * Filters: event_type, related_type, related_id, from_date, to_date, search
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = BusinessDiary::query();

        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }
        if (!empty($filters['related_type'])) {
            $query->where('related_type', $filters['related_type']);
        }
        if (!empty($filters['related_id'])) {
            $query->where('related_id', $filters['related_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('event_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('event_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 25);
        return $query->orderByDesc('event_date')->orderByDesc('id')->paginate($perPage);
    }

    public function store(array $data): BusinessDiary
    {
        return BusinessDiary::create([
            'event_date'   => Carbon::parse($data['event_date'])->toDateString(),
            'title'        => $data['title'],
            'notes'        => $data['notes'] ?? null,
            'event_type'   => $data['event_type'] ?? 'general',
            'related_type' => $data['related_type'] ?? null,
            'related_id'   => $data['related_id'] ?? null,
            'created_by'   => authId(),
        ]);
    }

    public function update(BusinessDiary $diary, array $data): BusinessDiary
    {
        if (!empty($data['event_date'])) {
            $data['event_date'] = Carbon::parse($data['event_date'])->toDateString();
        }
        $diary->update($data);
        return $diary->fresh();
    }

    public function destroy(BusinessDiary $diary): void
    {
        $diary->delete();
    }
}