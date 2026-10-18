<?php

namespace App\Services\Modules\Hrm\Payrolls;

use App\Models\SalaryComponent;
use Illuminate\Pagination\LengthAwarePaginator;

class SalaryComponentService
{
    protected $model;

    public function __construct()
    {
        $this->model = new SalaryComponent;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['payElement']);

        $query->when(isset($filters['employment_id']), function ($q) use ($filters) {
            $q->where('employment_id', $filters['employment_id']);
        })->when(isset($filters['pay_element_id']), function ($q) use ($filters) {
            $q->where('pay_element_id', $filters['pay_element_id']);
        })->when(isset($filters['is_percentage']), function ($q) use ($filters) {
            $q->where('is_percentage', $filters['is_percentage']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): SalaryComponent
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): SalaryComponent
    {

        $salaryComponent = $this->model->create($attributes);

        log_activity('Created a new salary component', authId(), null, 'salary_component', 'created', [
            'id' => $salaryComponent->id,
            'employment_id' => $salaryComponent->employment_id,
            'pay_element_id' => $salaryComponent->pay_element_id,
            'amount' => $salaryComponent->amount,
        ]);

        return $salaryComponent;
    }

    public function update(string $id, array $attributes): SalaryComponent
    {
        $salaryComponent = $this->model->findOrFail($id);
        $salaryComponent->update($attributes);

        log_activity('Updated salary component', authId(), null, 'salary_component', 'updated', $salaryComponent->getChanges() + ['id' => $salaryComponent->id]);

        return $salaryComponent->fresh();
    }

    public function destroy(string $id): void
    {
        $salaryComponent = $this->show($id);
        $salaryComponent->delete();
        log_activity('Deleted a salary component', authId(), null, 'salary_component', 'deleted', $salaryComponent->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery()->with(['payElement:id,name']);
        $query->when(isset($filters['employment_id']), function ($q) use ($filters) {
            $q->where('employment_id', $filters['employment_id']);
        })->when(isset($filters['pay_element_id']), function ($q) use ($filters) {
            $q->where('pay_element_id', $filters['pay_element_id']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'employment_id', 'pay_element_id', 'amount', 'is_percentage', 'effective_from')->latest()->limit($limit)->get();
    }
}
