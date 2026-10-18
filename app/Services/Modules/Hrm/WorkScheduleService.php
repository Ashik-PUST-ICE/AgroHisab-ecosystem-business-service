<?php

namespace App\Services\Modules\Hrm;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\WorkSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkScheduleService
{
    protected $model;

    public function __construct()
    {
        $this->model = new WorkSchedule;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model;

        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%'.$filters['search'].'%');
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('week_start_day', 'like', '%'.$filters['search'].'%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): WorkSchedule
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): WorkSchedule
    {
        $workSchedule = $this->model->create($attributes);

        log_activity('Created a new work schedule', authId(), null, 'work_schedule', 'created', [
            'id' => $workSchedule->id,
            'title' => $workSchedule->title,
        ]);

        return $workSchedule;
    }

    public function update(string $id, array $attributes): WorkSchedule
    {
        $workSchedule = $this->model->findOrFail($id);
        $workSchedule->update($attributes);

        log_activity('Updated work schedule', authId(), null, 'work_schedule', 'updated', $workSchedule->getChanges() + ['id' => $workSchedule->id]);

        return $workSchedule->fresh();
    }

    public function destroy(string $id): void
    {
        $workSchedule = $this->show($id);
        $workSchedule->delete();
        log_activity('Deleted a work schedule', authId(), null, 'work_schedule', 'deleted', $workSchedule->toArray());
    }

    public function status(string $id): WorkSchedule
    {
        $workSchedule = $this->show($id);
        $workSchedule->status = $workSchedule->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $workSchedule->save();
        log_activity('Toggled work schedule status to '.($workSchedule->status->label()), authId(), null, 'work_schedule', 'updated', $workSchedule->getChanges() + ['id' => $workSchedule->id]);

        return $workSchedule;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('title', 'like', '%'.$filters['search'].'%');
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('week_start_day', 'like', '%'.$filters['search'].'%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'title')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
