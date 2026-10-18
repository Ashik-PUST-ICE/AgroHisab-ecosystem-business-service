<?php

namespace App\Services\Modules\Hrm;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\EmployeeLoan;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeLoanService
{
    protected $model;

    public function __construct()
    {
        $this->model = new EmployeeLoan;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['employee_id']), function ($q) use ($filters) {
            $q->where('employee_id', $filters['employee_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        })->when(isset($filters['loan_code']), function ($q) use ($filters) {
            $q->where('loan_code', 'like', '%'.$filters['loan_code'].'%');
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): EmployeeLoan
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): EmployeeLoan
    {
        $employeeLoan = $this->model->create($attributes);

        log_activity('Created a new employee loan', authId(), null, 'employee_loan', 'created', [
            'id' => $employeeLoan->id,
            'employee_id' => $employeeLoan->employee_id,
            'loan_code' => $employeeLoan->loan_code,
            'principal' => $employeeLoan->principal,
        ]);

        return $employeeLoan;
    }

    public function update(string $id, array $attributes): EmployeeLoan
    {
        $employeeLoan = $this->model->findOrFail($id);
        $employeeLoan->update($attributes);

        log_activity('Updated employee loan', authId(), null, 'employee_loan', 'updated', $employeeLoan->getChanges() + ['id' => $employeeLoan->id]);

        return $employeeLoan->fresh();
    }

    public function destroy(string $id): void
    {
        $employeeLoan = $this->show($id);
        $employeeLoan->delete();
        log_activity('Deleted an employee loan', authId(), null, 'employee_loan', 'deleted', $employeeLoan->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['employee_id']), function ($q) use ($filters) {
            $q->where('employee_id', $filters['employee_id']);
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'loan_code', 'start_date')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }

    public function status(string $id): EmployeeLoan
    {
        $employeeLoan = $this->show($id);
        $employeeLoan->status = $employeeLoan->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $employeeLoan->save();
        log_activity('Toggled employeeLoan status to '.($employeeLoan->status->label()), authId(), null, 'employeeLoan', 'updated', $employeeLoan->getChanges() + ['id' => $employeeLoan->id]);

        return $employeeLoan;
    }
}
