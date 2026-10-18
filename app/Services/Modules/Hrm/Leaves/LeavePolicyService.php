<?php

namespace App\Services\Modules\Hrm\Leaves;

use App\Models\LeavePolicy;
use Illuminate\Pagination\LengthAwarePaginator;

class LeavePolicyService
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeavePolicy;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('fiscalYear', 'leaveType');

        $query->when(isset($filters['fiscal_year_id']), function ($q) use ($filters) {
            $q->where('fiscal_year_id', $filters['fiscal_year_id']);
        })->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['leave_type_id']), function ($q) use ($filters) {
            $q->where('leave_type_id', $filters['leave_type_id']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): LeavePolicy
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): LeavePolicy
    {
        $leavePolicy = $this->model->create($attributes);

        log_activity('Created a new leave policy', authId(), null, 'leave_policy', 'created', [
            'id' => $leavePolicy->id,
            'fiscal_year_id' => $leavePolicy->fiscal_year_id,
            'leave_type_id' => $leavePolicy->leave_type_id,
        ]);

        return $leavePolicy;
    }

    public function update(string $id, array $attributes): LeavePolicy
    {
        $leavePolicy = $this->model->findOrFail($id);
        $leavePolicy->update($attributes);

        log_activity('Updated leave policy', authId(), null, 'leave_policy', 'updated', $leavePolicy->getChanges() + ['id' => $leavePolicy->id]);

        return $leavePolicy->fresh();
    }

    public function destroy(string $id): void
    {
        $leavePolicy = $this->show($id);
        $leavePolicy->delete();
        log_activity('Deleted a leave policy', authId(), null, 'leave_policy', 'deleted', $leavePolicy->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['fiscal_year_id']), function ($q) use ($filters) {
            $q->where('fiscal_year_id', $filters['fiscal_year_id']);
        })->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['leave_type_id']), function ($q) use ($filters) {
            $q->where('leave_type_id', $filters['leave_type_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->whereHas('leaveType', function ($leaveTypeQuery) use ($filters) {
                $leaveTypeQuery->where('name', 'like', '%'.$filters['search'].'%');
            })->orWhereHas('fiscalYear', function ($fiscalYearQuery) use ($filters) {
                $fiscalYearQuery->where('name', 'like', '%'.$filters['search'].'%');
            });
        });

        $limit = $filters['limit'] ?? 10;

        return $query->with('fiscalYear:id,name', 'leaveType:id,name')->latest()->limit($limit)->get();
    }
}
