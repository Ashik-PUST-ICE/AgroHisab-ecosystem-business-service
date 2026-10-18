<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\RequisitionStatus\RequisitionStatusEnum;
use App\Models\Requisition;
use Illuminate\Pagination\LengthAwarePaginator;

class RequisitionService
{
    protected Requisition $model;

    public function __construct()
    {
        $this->model = new Requisition;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with([
            'stockCategory:id,name,slug',
            'product:id,name,slug',
            'brand:id,name,slug',
            'unit:id,name',
        ]);

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        $paginate = $query->latest()->paginate($filters['per_page'] ?? 10);
        $ids = $paginate->pluck('approved_by')->merge($paginate->pluck('created_by'))->filter()->unique()->values()->all();
        user()->warmUp($ids);

        return $paginate;
    }

    public function store(array $data): Requisition
    {
        $requisition = $this->model->create($data);
        log_activity('Created a new requisition', authId(), null, 'requisition', 'created', [
            'id' => $requisition->id,
            'created_by' => $requisition->created_by,
        ]);

        return $requisition;
    }

    public function show(string $id, $relations = [], $throwException = true): Requisition
    {
        $query = $this->model->with($relations);

        return $throwException ? $query->findOrFail($id) : $query->find($id);
    }

    public function update(string $id, array $data): Requisition
    {
        $requisition = $this->show($id);
        $requisition->update($data);
        log_activity('Updated a requisition', authId(), null, 'requisition', 'updated', $requisition->getChanges() + ['id' => $requisition->id]);

        return $requisition;
    }

    public function destroy(string $id): void
    {
        $requisition = $this->show($id);
        $requisition->delete();

        log_activity('Deleted a requisition', authId(), null, 'requisition', 'deleted', $requisition->toArray());
    }

    public function approve(string $id, string $approvalType, ?string $comment = null): Requisition
    {
        $requisition = $this->show($id);

        $authUserId = authId();

        if ($approvalType === 'approve') {

            if ($requisition->status !== RequisitionStatusEnum::PENDING) {
                throw new \Exception("Only pending requisitions can be approved. Current status: {$requisition->status?->label()}.");
            }
            $requisition->approved_by = $authUserId;
            $requisition->status = RequisitionStatusEnum::APPROVED;
            $requisition->comment = $comment;
            $logMessage = 'Approved requisition';
        } elseif ($approvalType === 'cancel') {

            if ($requisition->status !== RequisitionStatusEnum::PENDING) {
                throw new \Exception("Only pending requisitions can be cancelled. Current status: {$requisition->status?->label()}.");
            }
            $requisition->approved_by = $authUserId;
            $requisition->status = RequisitionStatusEnum::CANCELLED;
            $requisition->comment = $comment;
            $logMessage = 'Cancelled requisition';
        } else {
            throw new \Exception('Invalid approval type. Use "approve" or "cancel".');
        }

        $requisition->save();

        log_activity($logMessage, $authUserId, null, 'requisition', $approvalType === 'cancel' ? 'cancelled' : 'approved', $requisition->getChanges() + ['id' => $requisition->id]);

        return $requisition->fresh();
    }





}
