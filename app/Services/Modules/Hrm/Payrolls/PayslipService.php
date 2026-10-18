<?php

namespace App\Services\Modules\Hrm\Payrolls;

use App\Models\Payslip;
use Illuminate\Pagination\LengthAwarePaginator;

class PayslipService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Payslip;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['payrollRun']);

        $query->when(isset($filters['payroll_run_id']), function ($q) use ($filters) {
            $q->where('payroll_run_id', $filters['payroll_run_id']);
        })->when(isset($filters['employee_id']), function ($q) use ($filters) {
            $q->where('employee_id', $filters['employee_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): Payslip
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): Payslip
    {
        $payslip = $this->model->create($attributes);

        log_activity('Created a new payslip', authId(), null, 'payslip', 'created', [
            'id' => $payslip->id,
            'payroll_run_id' => $payslip->payroll_run_id,
            'employee_id' => $payslip->employee_id,
            'net_pay' => $payslip->net_pay,
        ]);

        return $payslip;
    }

    public function update(string $id, array $attributes): Payslip
    {
        $payslip = $this->model->findOrFail($id);
        $payslip->update($attributes);

        log_activity('Updated payslip', authId(), null, 'payslip', 'updated', $payslip->getChanges() + ['id' => $payslip->id]);

        return $payslip->fresh();
    }

    public function destroy(string $id): void
    {
        $payslip = $this->show($id);
        $payslip->delete();
        log_activity('Deleted a payslip', authId(), null, 'payslip', 'deleted', $payslip->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['payroll_run_id']), function ($q) use ($filters) {
            $q->where('payroll_run_id', $filters['payroll_run_id']);
        })->when(isset($filters['employee_id']), function ($q) use ($filters) {
            $q->where('employee_id', $filters['employee_id']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'employee_id', 'employment_id', 'gross_pay', 'status')->latest()->limit($limit)->get();
    }

    public function status(string $id): Payslip
    {
        $payslip = $this->show($id);
        $payslip->status = $payslip->status->nextStatus();

        $payslip->save();
        log_activity('Toggled payslip status to '.$payslip->status->label(), authId(), null, 'payslip', 'updated', $payslip->getChanges() + ['id' => $payslip->id]);

        return $payslip;
    }
}
