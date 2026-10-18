<?php

namespace App\Services\Modules\Finance;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\FinanceCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceCategoryService
{
    protected FinanceCategory $model;

    public function __construct()
    {
        $this->model = new FinanceCategory;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): FinanceCategory
    {
        $FinanceCategory = $this->model->create($data);
        log_activity('Created a new FinanceCategory', authId(), null, 'finance_category', 'created', ['id' => $FinanceCategory->id, 'title' => $FinanceCategory->title]);

        return $FinanceCategory;
    }

    public function show(string $id, $relations = [], $throwException = true): FinanceCategory
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): FinanceCategory
    {
        $FinanceCategory = $this->show($id);
        $FinanceCategory->update($data);
        log_activity('Updated a FinanceCategory', authId(), null, 'finance_category', 'updated', $FinanceCategory->getChanges() + ['id' => $FinanceCategory->id]);

        return $FinanceCategory;
    }

    public function destroy(string $id): void
    {
        $FinanceCategory = $this->show($id);
        $FinanceCategory->delete();
        log_activity('Deleted a FinanceCategory', authId(), null, 'finance_category', 'deleted', $FinanceCategory->toArray());
    }

    public function toggleStatus(string $id): FinanceCategory
    {
        $FinanceCategory = $this->show($id);
        $FinanceCategory->status = $FinanceCategory->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $FinanceCategory->save();
        log_activity('Toggled FinanceCategory status to '.($FinanceCategory->status->label()), authId(), null, 'finance_category', 'updated', $FinanceCategory->getChanges() + ['id' => $FinanceCategory->id]);

        return $FinanceCategory;
    }
}
