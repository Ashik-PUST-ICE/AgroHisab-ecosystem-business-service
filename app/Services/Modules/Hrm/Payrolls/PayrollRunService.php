<?php

namespace App\Services\Modules\Hrm\Payrolls;

use App\Enums\Commons\PayrollRun\PayrollRunStatusEnum;
use App\Models\PayrollRun;
use Illuminate\Pagination\LengthAwarePaginator;

class PayrollRunService
{
    protected $model;

    public function __construct()
    {
        $this->model = new PayrollRun;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['payslips']);

        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        })->when(isset($filters['period_start']), function ($q) use ($filters) {
            $q->whereDate('period_start', '>=', $filters['period_start']);
        })->when(isset($filters['period_end']), function ($q) use ($filters) {
            $q->whereDate('period_end', '<=', $filters['period_end']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): PayrollRun
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): PayrollRun
    {
        $attributes['created_by'] = authId();
        $payrollRun = $this->model->create($attributes);

        log_activity('Created a new payroll run', authId(), null, 'payroll_run', 'created', [
            'id' => $payrollRun->id,
            'run_code' => $payrollRun->run_code,
            'period_start' => $payrollRun->period_start,
            'period_end' => $payrollRun->period_end,
        ]);

        return $payrollRun;
    }

    public function update(string $id, array $attributes): PayrollRun
    {
        $payrollRun = $this->model->findOrFail($id);
        $payrollRun->update($attributes);

        log_activity('Updated payroll run', authId(), null, 'payroll_run', 'updated', $payrollRun->getChanges() + ['id' => $payrollRun->id]);

        return $payrollRun->fresh();
    }

    public function destroy(string $id): void
    {
        $payrollRun = $this->show($id);
        $payrollRun->delete();
        log_activity('Deleted a payroll run', authId(), null, 'payroll_run', 'deleted', $payrollRun->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'run_code', 'period_start', 'period_end', 'status')->latest()->limit($limit)->get();
    }

    public function status(string $id): PayrollRun
    {
        $payrollRun = $this->show($id);
        $currentStatus = $payrollRun->status;

        $payrollRun->status = match ($currentStatus) {
            PayrollRunStatusEnum::DRAFT => PayrollRunStatusEnum::CALCULATED->value,
            PayrollRunStatusEnum::CALCULATED => PayrollRunStatusEnum::LOCKED->value,
            PayrollRunStatusEnum::LOCKED => PayrollRunStatusEnum::POSTED->value,
            PayrollRunStatusEnum::POSTED => PayrollRunStatusEnum::DRAFT->value,
            default => PayrollRunStatusEnum::DRAFT->value,
        };

        $payrollRun->save();
        log_activity('Toggled payroll run status to '.($payrollRun->status->label()), authId(), null, 'payroll_run', 'updated', $payrollRun->getChanges() + ['id' => $payrollRun->id]);

        return $payrollRun;
    }
}
