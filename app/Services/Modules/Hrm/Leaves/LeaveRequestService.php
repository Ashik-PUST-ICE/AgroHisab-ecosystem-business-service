<?php

namespace App\Services\Modules\Hrm\Leaves;

use App\Enums\Commons\LeaveRequests\LeaveRequestsEnum;
use App\Models\LeaveRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRequestService
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveRequest;
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
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        })->when(isset($filters['applied_to']), function ($q) use ($filters) {
            $q->where('applied_to', $filters['applied_to']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): LeaveRequest
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): LeaveRequest
    {
        $leaveRequest = $this->model->create($attributes);

        log_activity('Created a new leave request', authId(), null, 'leave_request', 'created', [
            'id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'start_date' => $leaveRequest->start_date,
            'end_date' => $leaveRequest->end_date,
            'days' => $leaveRequest->days,
        ]);

        return $leaveRequest;
    }

    public function update(string $id, array $attributes): LeaveRequest
    {
        $leaveRequest = $this->model->findOrFail($id);
        $leaveRequest->update($attributes);

        log_activity('Updated leave request', authId(), null, 'leave_request', 'updated', $leaveRequest->getChanges() + ['id' => $leaveRequest->id]);

        return $leaveRequest->fresh();
    }

    public function destroy(string $id): void
    {
        $leaveRequest = $this->show($id);
        $leaveRequest->delete();
        log_activity('Deleted a leave request', authId(), null, 'leave_request', 'deleted', $leaveRequest->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery()->with(['leaveType:id,name', 'fiscalYear:id,name']);
        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['leave_type_id']), function ($q) use ($filters) {
            $q->where('leave_type_id', $filters['leave_type_id']);
        })->when(isset($filters['fiscal_year_id']), function ($q) use ($filters) {
            $q->where('fiscal_year_id', $filters['fiscal_year_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->latest()->limit($limit)->get();
    }

    public function status(string $id): LeaveRequest
    {
        $leaveRequest = $this->show($id);
        $leaveRequest->status = match ($leaveRequest->status) {
            LeaveRequestsEnum::PENDING => LeaveRequestsEnum::APPROVED,
            LeaveRequestsEnum::APPROVED => LeaveRequestsEnum::REJECTED,
            LeaveRequestsEnum::REJECTED => LeaveRequestsEnum::PENDING,
        };
        $leaveRequest->save();
        log_activity('Toggled leave request status to '.($leaveRequest->status->label()), authId(), null, 'leave_request', 'updated', $leaveRequest->getChanges() + ['id' => $leaveRequest->id]);

        return $leaveRequest;
    }
}
