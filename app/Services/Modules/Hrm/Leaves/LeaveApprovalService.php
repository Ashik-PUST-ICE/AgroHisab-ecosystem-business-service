<?php

namespace App\Services\Modules\Hrm\Leaves;

use App\Enums\Commons\LeaveApprovals\LeaveApprovalsEnum;
use App\Models\LeaveApproval;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveApprovalService
{
    protected $model;

    public function __construct()
    {
        $this->model = new LeaveApproval;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['leaveRequest']);

        $query->when(isset($filters['leave_request_id']), function ($q) use ($filters) {
            $q->where('leave_request_id', $filters['leave_request_id']);
        })->when(isset($filters['approver_id']), function ($q) use ($filters) {
            $q->where('approver_id', $filters['approver_id']);
        })->when(isset($filters['action']), function ($q) use ($filters) {
            $q->where('action', $filters['action']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): LeaveApproval
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): LeaveApproval
    {
        $leaveApproval = $this->model->create($attributes);

        log_activity('Created a new leave approval', authId(), null, 'leave_approval', 'created', [
            'id' => $leaveApproval->id,
            'leave_request_id' => $leaveApproval->leave_request_id,
            'approver_id' => $leaveApproval->approver_id,
            'action' => $leaveApproval->action,
        ]);

        return $leaveApproval;
    }

    public function update(string $id, array $attributes): LeaveApproval
    {
        $leaveApproval = $this->model->findOrFail($id);
        $leaveApproval->update($attributes);

        log_activity('Updated leave approval', authId(), null, 'leave_approval', 'updated', $leaveApproval->getChanges() + ['id' => $leaveApproval->id]);

        return $leaveApproval->fresh();
    }

    public function destroy(string $id): void
    {
        $leaveApproval = $this->show($id);
        $leaveApproval->delete();
        log_activity('Deleted a leave approval', authId(), null, 'leave_approval', 'deleted', $leaveApproval->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery()->with(['leaveRequest:id,user_id,leave_type_id,start_date,end_date']);
        $query->when(isset($filters['leave_request_id']), function ($q) use ($filters) {
            $q->where('leave_request_id', $filters['leave_request_id']);
        })->when(isset($filters['approver_id']), function ($q) use ($filters) {
            $q->where('approver_id', $filters['approver_id']);
        })->when(isset($filters['action']), function ($q) use ($filters) {
            $q->where('action', $filters['action']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->latest()->limit($limit)->get();
    }

    public function status(string $id): LeaveApproval
    {
        $leaveApproval = $this->show($id);
        $leaveApproval->action = match ($leaveApproval->action) {
            LeaveApprovalsEnum::PENDING => LeaveApprovalsEnum::APPROVED,
            LeaveApprovalsEnum::APPROVED => LeaveApprovalsEnum::REJECTED,
            LeaveApprovalsEnum::REJECTED => LeaveApprovalsEnum::PENDING,
        };
        $leaveApproval->save();
        log_activity('Toggled leave request status to '.($leaveApproval->action->label()), authId(), null, 'leave_approval', 'updated', $leaveApproval->getChanges() + ['id' => $leaveApproval->id]);

        return $leaveApproval;
    }
}
