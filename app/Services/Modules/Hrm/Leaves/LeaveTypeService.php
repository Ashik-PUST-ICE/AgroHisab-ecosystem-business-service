<?php

namespace App\Services\Modules\Hrm\Leaves;

use App\Models\LeaveType;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveTypeService
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveType;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model;

        $query->when(isset($filters['company_id']), function ($q) use ($filters) {
            $q->where('company_id', $filters['company_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['search'].'%')
                ->orWhere('code', 'like', '%'.$filters['search'].'%');
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): LeaveType
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): LeaveType
    {
        $leaveType = $this->model->create($attributes);

        log_activity('Created a new leave type', authId(), null, 'leave_type', 'created', [
            'id' => $leaveType->id,
            'name' => $leaveType->name,
            'code' => $leaveType->code,
        ]);

        return $leaveType;
    }

    public function update(string $id, array $attributes): LeaveType
    {
        $leaveType = $this->model->findOrFail($id);
        $leaveType->update($attributes);

        log_activity('Updated leave type', authId(), null, 'leave_type', 'updated', $leaveType->getChanges() + ['id' => $leaveType->id]);

        return $leaveType->fresh();
    }

    public function destroy(string $id): void
    {
        $leaveType = $this->show($id);
        $leaveType->delete();
        log_activity('Deleted a leave type', authId(), null, 'leave_type', 'deleted', $leaveType->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['company_id']), function ($q) use ($filters) {
            $q->where('company_id', $filters['company_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['search'].'%')
                ->orWhere('code', 'like', '%'.$filters['search'].'%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'name', 'code')->latest()->limit($limit)->get();
    }
}
