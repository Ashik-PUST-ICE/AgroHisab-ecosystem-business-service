<?php

namespace App\Services\Modules\Hrm;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Shift;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Shift;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('workSchedule');

        $query->when(isset($filters['work_schedule_id']), function ($q) use ($filters) {
            $q->where('work_schedule_id', $filters['work_schedule_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%'.$filters['search'].'%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): Shift
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): Shift
    {
        $shift = $this->model->create($attributes);

        log_activity('Created a new shift', authId(), null, 'shift', 'created', [
            'id' => $shift->id,
            'title' => $shift->title,
        ]);

        return $shift;
    }

    public function update(string $id, array $attributes): Shift
    {
        $shift = $this->model->findOrFail($id);
        $shift->update($attributes);

        log_activity('Updated shift', authId(), null, 'shift', 'updated', $shift->getChanges() + ['id' => $shift->id]);

        return $shift->fresh();
    }

    public function destroy(string $id): void
    {
        $shift = $this->show($id);
        $shift->delete();
        log_activity('Deleted a shift', authId(), null, 'shift', 'deleted', $shift->toArray());
    }

    public function status(string $id): Shift
    {
        $shift = $this->show($id);
        $shift->status = $shift->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $shift->save();
        log_activity('Toggled shift status to '.($shift->status->label()), authId(), null, 'shift', 'updated', $shift->getChanges() + ['id' => $shift->id]);

        return $shift;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['work_schedule_id']), function ($q) use ($filters) {
            $q->where('work_schedule_id', $filters['work_schedule_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%'.$filters['search'].'%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
