<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandService
{
    protected Brand $model;

    public function __construct()
    {
        $this->model = new Brand;
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

    public function store(array $data): Brand
    {

        $brand = $this->model->create($data);
        log_activity('Created a new brand', authId(), null, 'brand', 'created',
            ['id' => $brand->id, 'name' => $brand->name]);

        return $brand;
    }

    public function show(string $id, $relations = [], $throwException = true): Brand
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Brand
    {
        $brand = $this->show($id);
        $brand->update($data);
        log_activity('Updated a brand', authId(), null, 'brand', 'updated', $brand->getChanges() + ['id' => $brand->id]);

        return $brand;
    }

    public function destroy(string $id): void
    {
        $brand = $this->show($id);
        $brand->delete();
        log_activity('Deleted a brand ', authId(), null, 'brand', 'deleted', $brand->toArray());
    }

    public function toggleStatus(string $id): Brand
    {
        $brand = $this->show($id);
        $brand->status = $brand->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $brand->save();
        log_activity('Toggled brand status to '.($brand->status->label()), authId(), null, 'brand', 'updated', $brand->getChanges() + ['id' => $brand->id]);

        return $brand;
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
