<?php

namespace App\Services\Modules\Hrm\Holidays;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Holidays;
use Illuminate\Pagination\LengthAwarePaginator;

class HolidayService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Holidays;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['holidayGroup']);

        $query->when(isset($filters['holiday_group_id']), function ($q) use ($filters) {
            $q->where('holiday_group_id', $filters['holiday_group_id']);
        })->when(isset($filters['name']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['name'].'%');
        })->when(isset($filters['scope_type']), function ($q) use ($filters) {
            $q->where('scope_type', $filters['scope_type']);
        })->when(isset($filters['scope_id']), function ($q) use ($filters) {
            $q->where('scope_id', $filters['scope_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        })->when(isset($filters['start_date']), function ($q) use ($filters) {
            $q->whereDate('start_date', '>=', $filters['start_date']);
        })->when(isset($filters['end_date']), function ($q) use ($filters) {
            $q->whereDate('end_date', '<=', $filters['end_date']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): Holidays
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): Holidays
    {
        $holiday = $this->model->create($attributes);

        log_activity('Created a new holiday', authId(), null, 'holiday', 'created', [
            'id' => $holiday->id,
            'name' => $holiday->name,
            'holiday_group_id' => $holiday->holiday_group_id,
            'start_date' => $holiday->start_date,
            'end_date' => $holiday->end_date,
        ]);

        return $holiday;
    }

    public function update(string $id, array $attributes): Holidays
    {
        $holiday = $this->model->findOrFail($id);
        $holiday->update($attributes);

        log_activity('Updated holiday', authId(), null, 'holiday', 'updated', $holiday->getChanges() + ['id' => $holiday->id]);

        return $holiday->fresh();
    }

    public function destroy(string $id): void
    {
        $holiday = $this->show($id);
        $holiday->delete();
        log_activity('Deleted a holiday', authId(), null, 'holiday', 'deleted', $holiday->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['holiday_group_id']), function ($q) use ($filters) {
            $q->where('holiday_group_id', $filters['holiday_group_id']);
        })->when(isset($filters['scope_type']), function ($q) use ($filters) {
            $q->where('scope_type', $filters['scope_type']);
        })->when(isset($filters['scope_id']), function ($q) use ($filters) {
            $q->where('scope_id', $filters['scope_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->with(['holidayGroup:id,name'])->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();

    }

    public function status(string $id): Holidays
    {
        $holiday = $this->show($id);
        $holiday->status = $holiday->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $holiday->save();
        log_activity('Toggled holiday status to '.($holiday->status->label()), authId(), null, 'holiday', 'updated', $holiday->getChanges() + ['id' => $holiday->id]);

        return $holiday;
    }
}
