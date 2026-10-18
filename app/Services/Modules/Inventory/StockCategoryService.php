<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\StockCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class StockCategoryService
{
    protected StockCategory $model;

    public function __construct()
    {
        $this->model = new StockCategory;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): StockCategory
    {

        $stockCategory = $this->model->create($data);
        log_activity('Created a new stock category', authId(), null, 'stock_category', 'created', [
            'id' => $stockCategory->id, 'name' => $stockCategory->name]);

        return $stockCategory;
    }

    public function show(string $id, $relations = [], $throwException = true): StockCategory
    {
        $query = $this->model->with(array_merge(['parent', 'children'], $relations));
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): StockCategory
    {
        $stockCategory = $this->show($id);
        $stockCategory->update($data);
        log_activity('Updated a stock category', authId(), null, 'stock_category', 'updated', $stockCategory->getChanges() + ['id' => $stockCategory->id]);

        return $stockCategory;
    }

    public function destroy(string $id): void
    {
        $stockCategory = $this->show($id);
        $stockCategory->delete();
        log_activity('Deleted a stock category ', authId(), null, 'stock_category', 'deleted', $stockCategory->toArray());
    }

    public function toggleStatus(string $id): StockCategory
    {
        $stockCategory = $this->show($id);
        $stockCategory->status = $stockCategory->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $stockCategory->save();
        log_activity('Toggled stock category status to '.($stockCategory->status->label()), authId(), null, 'stock_category', 'updated', $stockCategory->getChanges() + ['id' => $stockCategory->id]);

        return $stockCategory;
    }

    public function list(array $filters = []): array
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%'.$filters['search'].'%');
        });

        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        }, function ($q) {
            $q->where('status', StatusEnum::ACTIVE);
        });

        $brands = $query->select('id', 'name', 'status')->orderBy('name')->get();

        return $brands->map(function ($brand) {
            return [
                'value' => $brand->id,
                'label' => $brand->name,
            ];
        })->toArray();
    }
}
