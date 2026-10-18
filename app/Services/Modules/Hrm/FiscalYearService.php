<?php

namespace App\Services\Modules\Hrm;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\FiscalYear;
use Illuminate\Pagination\LengthAwarePaginator;

class FiscalYearService
{
    protected $model;

    public function __construct()
    {
        $this->model = new FiscalYear;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model;

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['search'].'%')
                ->orWhere('type', 'like', '%'.$filters['search'].'%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): FiscalYear
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): FiscalYear
    {
        $fiscalYear = $this->model->create($attributes);

        log_activity('Created a new fiscal year', authId(), null, 'fiscal_year', 'created', [
            'id' => $fiscalYear->id,
            'name' => $fiscalYear->name,
            'type' => $fiscalYear->type,
        ]);

        return $fiscalYear;
    }

    public function update(string $id, array $attributes): FiscalYear
    {
        $fiscalYear = $this->model->findOrFail($id);
        $fiscalYear->update($attributes);

        log_activity('Updated fiscal year', authId(), null, 'fiscal_year', 'updated', $fiscalYear->getChanges() + ['id' => $fiscalYear->id]);

        return $fiscalYear->fresh();
    }

    public function destroy(string $id): void
    {
        $fiscalYear = $this->show($id);
        $fiscalYear->delete();
        log_activity('Deleted a fiscal year', authId(), null, 'fiscal_year', 'deleted', $fiscalYear->toArray());
    }

    public function status(string $id): FiscalYear
    {
        $fiscalYear = $this->show($id);
        $fiscalYear->status = $fiscalYear->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $fiscalYear->save();
        log_activity('Toggled fiscal year status to '.($fiscalYear->status->label()), authId(), null, 'fiscal_year', 'updated', $fiscalYear->getChanges() + ['id' => $fiscalYear->id]);

        return $fiscalYear;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['search'].'%')
                ->orWhere('type', 'like', '%'.$filters['search'].'%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'name', 'type')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
