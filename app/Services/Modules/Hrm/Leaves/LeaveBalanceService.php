<?php

namespace App\Services\Modules\Hrm\Leaves;

use App\Models\LeaveBalance;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveBalanceService
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveBalance;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['leaveType', 'fiscalYear']);

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['leave_type_id']), function ($q) use ($filters) {
            $q->where('leave_type_id', $filters['leave_type_id']);
        })->when(isset($filters['fiscal_year_id']), function ($q) use ($filters) {
            $q->where('fiscal_year_id', $filters['fiscal_year_id']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): LeaveBalance
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): LeaveBalance
    {
        $leaveBalance = $this->model->create($attributes);

        log_activity('Created a new leave balance', authId(), null, 'leave_balance', 'created', [
            'id' => $leaveBalance->id,
            'user_id' => $leaveBalance->user_id,
            'leave_type_id' => $leaveBalance->leave_type_id,
            'balance' => $leaveBalance->balance,
        ]);

        return $leaveBalance;
    }

    public function update(string $id, array $attributes): LeaveBalance
    {
        $leaveBalance = $this->model->findOrFail($id);
        $leaveBalance->update($attributes);

        log_activity('Updated leave balance', authId(), null, 'leave_balance', 'updated', $leaveBalance->getChanges() + ['id' => $leaveBalance->id]);

        return $leaveBalance->fresh();
    }

    public function destroy(string $id): void
    {
        $leaveBalance = $this->show($id);
        $leaveBalance->delete();
        log_activity('Deleted a leave balance', authId(), null, 'leave_balance', 'deleted', $leaveBalance->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['leave_type_id']), function ($q) use ($filters) {
            $q->where('leave_type_id', $filters['leave_type_id']);
        })->when(isset($filters['fiscal_year_id']), function ($q) use ($filters) {
            $q->where('fiscal_year_id', $filters['fiscal_year_id']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->with(['leaveType:id,name', 'fiscalYear:id,name'])->latest()->limit($limit)->get();
    }
}
