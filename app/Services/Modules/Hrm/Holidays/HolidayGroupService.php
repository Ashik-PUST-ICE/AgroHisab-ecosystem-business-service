<?php

namespace App\Services\Modules\Hrm\Holidays;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\HolidayGroup;
use Illuminate\Pagination\LengthAwarePaginator;

class HolidayGroupService
{
    protected $model;

    public function __construct()
    {
        $this->model = new HolidayGroup;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->query();

        $query->when(isset($filters['company_id']), function ($q) use ($filters) {
            $q->where('company_id', $filters['company_id']);
        })->when(isset($filters['name']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['name'].'%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): HolidayGroup
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): HolidayGroup
    {
        $holidayGroup = $this->model->create($attributes);

        log_activity('Created a new holiday group', authId(), null, 'holiday_group', 'created', [
            'id' => $holidayGroup->id,
            'name' => $holidayGroup->name,
            'company_id' => $holidayGroup->company_id,
        ]);

        return $holidayGroup;
    }

    public function update(string $id, array $attributes): HolidayGroup
    {
        $holidayGroup = $this->model->findOrFail($id);
        $holidayGroup->update($attributes);

        log_activity('Updated holiday group', authId(), null, 'holiday_group', 'updated', $holidayGroup->getChanges() + ['id' => $holidayGroup->id]);

        return $holidayGroup->fresh();
    }

    public function destroy(string $id): void
    {
        $holidayGroup = $this->show($id);
        $holidayGroup->delete();
        log_activity('Deleted a holiday group', authId(), null, 'holiday_group', 'deleted', $holidayGroup->toArray());
    }

    public function status(string $id): HolidayGroup
    {
        $holidayGroup = $this->show($id);
        $holidayGroup->status = $holidayGroup->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $holidayGroup->save();
        log_activity('Toggled holiday group status to '.($holidayGroup->status->label()), authId(), null, 'holiday_group', 'updated', $holidayGroup->getChanges() + ['id' => $holidayGroup->id]);

        return $holidayGroup;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['company_id']), function ($q) use ($filters) {
            $q->where('company_id', $filters['company_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'name')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
