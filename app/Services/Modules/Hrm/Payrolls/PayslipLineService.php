<?php

namespace App\Services\Modules\Hrm\Payrolls;

use App\Models\PayslipLine;
use Illuminate\Pagination\LengthAwarePaginator;

class PayslipLineService
{
    protected $model;

    public function __construct()
    {
        $this->model = new PayslipLine;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['payElement', 'payslip']);

        $query->when(isset($filters['payslip_id']), function ($q) use ($filters) {
            $q->where('payslip_id', $filters['payslip_id']);
        })->when(isset($filters['pay_element_id']), function ($q) use ($filters) {
            $q->where('pay_element_id', $filters['pay_element_id']);
        })->when(isset($filters['is_earning']), function ($q) use ($filters) {
            $q->where('is_earning', $filters['is_earning']);
        })->when(isset($filters['taxable']), function ($q) use ($filters) {
            $q->where('taxable', $filters['taxable']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): PayslipLine
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): PayslipLine
    {
        $payslipLine = $this->model->create($attributes);

        log_activity('Created a new payslip line', authId(), null, 'payslip_line', 'created', [
            'id' => $payslipLine->id,
            'payslip_id' => $payslipLine->payslip_id,
            'pay_element_id' => $payslipLine->pay_element_id,
            'amount' => $payslipLine->amount,
        ]);

        return $payslipLine;
    }

    public function update(string $id, array $attributes): PayslipLine
    {
        $payslipLine = $this->model->findOrFail($id);
        $payslipLine->update($attributes);

        log_activity('Updated payslip line', authId(), null, 'payslip_line', 'updated', $payslipLine->getChanges() + ['id' => $payslipLine->id]);

        return $payslipLine->fresh();
    }

    public function destroy(string $id): void
    {
        $payslipLine = $this->show($id);
        $payslipLine->delete();
        log_activity('Deleted a payslip line', authId(), null, 'payslip_line', 'deleted', $payslipLine->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['payslip_id']), function ($q) use ($filters) {
            $q->where('payslip_id', $filters['payslip_id']);
        })->when(isset($filters['pay_element_id']), function ($q) use ($filters) {
            $q->where('pay_element_id', $filters['pay_element_id']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'amount', 'is_earning', 'taxable', 'quantity')->latest()->limit($limit)->get();
    }
}
