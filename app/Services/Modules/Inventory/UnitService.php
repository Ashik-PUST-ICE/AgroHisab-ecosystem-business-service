<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitService
{
    protected Unit $model;

    public function __construct()
    {
        $this->model = new Unit;
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

    public function store(array $data): Unit
    {

        $unit = $this->model->create($data);
        log_activity('Created a new unit', authId(), null, 'unit', 'created', [
            'id' => $unit->id,
            'name' => $unit->name,
        ]);

        return $unit;
    }

    public function show(string $id, $relations = [], $throwException = true): Unit
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Unit
    {
        $unit = $this->show($id);
        $unit->update($data);
        log_activity('Updated a unit', authId(), null, 'unit', 'updated', $unit->getChanges() + ['id' => $unit->id]);

        return $unit;
    }

    public function destroy(string $id): void
    {
        $unit = $this->show($id);
        $unit->delete();
        log_activity('Deleted a unit ', authId(), null, 'unit', 'deleted', $unit->toArray());
    }

    public function toggleStatus(string $id): Unit
    {
        $unit = $this->show($id);
        $unit->status = $unit->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $unit->save();
        log_activity('Toggled unit status to '.($unit->status->label()), authId(), null, 'unit', 'updated', $unit->getChanges() + ['id' => $unit->id]);

        return $unit;
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
