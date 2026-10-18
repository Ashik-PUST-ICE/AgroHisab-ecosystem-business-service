<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorService
{
    protected Vendor $model;

    public function __construct()
    {
        $this->model = new Vendor;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('last_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('phone', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Vendor
    {

        $vendor = $this->model->create($data);
        log_activity('Created a new vendor', authId(), null, 'vendor', 'created', [
            'id' => $vendor->id,
            'first_name' => $vendor->first_name,
            'last_name' => $vendor->last_name,
        ]);

        return $vendor;
    }

    public function show(string $id, $relations = [], $throwException = true): Vendor
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Vendor
    {
        $vendor = $this->show($id);
        $vendor->update($data);
        log_activity('Updated a vendor', authId(), null, 'vendor', 'updated', $vendor->getChanges() + ['id' => $vendor->id]);

        return $vendor;
    }

    public function destroy(string $id): void
    {
        $vendor = $this->show($id);
        $vendor->delete();
        log_activity('Deleted a vendor ', authId(), null, 'vendor', 'deleted', $vendor->toArray());
    }

    public function toggleStatus(string $id): Vendor
    {
        $vendor = $this->show($id);
        $vendor->status = $vendor->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $vendor->save();
        log_activity('Toggled vendor status to '.($vendor->status->label()), authId(), null, 'vendor', 'updated', $vendor->getChanges() + ['id' => $vendor->id]);

        return $vendor;
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

        $brands = $query->select('id', 'first_name', 'last_name', 'status')->orderBy('first_name')->get();

        return $brands->map(function ($brand) {
            return [
                'value' => $brand->id,
                'label' => $brand->full_name,
            ];
        })->toArray();
    }
}
