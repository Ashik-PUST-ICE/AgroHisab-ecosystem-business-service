<?php

namespace App\Services\Modules\Hrm\Payrolls;

use App\Models\PayrollAdjustment;
use Illuminate\Pagination\LengthAwarePaginator;

class PayrollAdjustmentService
{
    protected $model;

    public function __construct()
    {
        $this->model = new PayrollAdjustment;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['payslip', 'payrollRun']);

        $query->when(isset($filters['payslip_id']), function ($q) use ($filters) {
            $q->where('payslip_id', $filters['payslip_id']);
        })->when(isset($filters['payroll_run_id']), function ($q) use ($filters) {
            $q->where('payroll_run_id', $filters['payroll_run_id']);
        })->when(isset($filters['employee_id']), function ($q) use ($filters) {
            $q->where('employee_id', $filters['employee_id']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): PayrollAdjustment
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): PayrollAdjustment
    {
        $attributes['created_by'] = authId();
        $payrollAdjustment = $this->model->create($attributes);

        log_activity('Created a new payroll adjustment', authId(), null, 'payroll_adjustment', 'created', [
            'id' => $payrollAdjustment->id,
            'payslip_id' => $payrollAdjustment->payslip_id,
            'payroll_run_id' => $payrollAdjustment->payroll_run_id,
            'employee_id' => $payrollAdjustment->employee_id,
            'amount' => $payrollAdjustment->amount,
        ]);

        return $payrollAdjustment;
    }

    public function update(string $id, array $attributes): PayrollAdjustment
    {
        $payrollAdjustment = $this->model->findOrFail($id);
        $attributes['created_by'] = authId();
        $payrollAdjustment->update($attributes);

        log_activity('Updated payroll adjustment', authId(), null, 'payroll_adjustment', 'updated', $payrollAdjustment->getChanges() + ['id' => $payrollAdjustment->id]);

        return $payrollAdjustment->fresh();
    }

    public function destroy(string $id): void
    {
        $payrollAdjustment = $this->show($id);
        $payrollAdjustment->delete();
        log_activity('Deleted a payroll adjustment', authId(), null, 'payroll_adjustment', 'deleted', $payrollAdjustment->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['payslip_id']), function ($q) use ($filters) {
            $q->where('payslip_id', $filters['payslip_id']);
        })->when(isset($filters['payroll_run_id']), function ($q) use ($filters) {
            $q->where('payroll_run_id', $filters['payroll_run_id']);
        })->when(isset($filters['employee_id']), function ($q) use ($filters) {
            $q->where('employee_id', $filters['employee_id']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'reason', 'amount')->latest()->limit($limit)->get();
    }
}
