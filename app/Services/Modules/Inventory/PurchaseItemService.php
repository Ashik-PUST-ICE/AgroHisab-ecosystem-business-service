<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\PurchaseItem;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseItemService
{
    protected PurchaseItem $model;

    public function __construct()
    {
        $this->model = new PurchaseItem;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('serial_no', 'like', '%'.$filters['search'].'%')
                    ->orWhere('mac_address', 'like', '%'.$filters['search'].'%')
                    ->orWhere('warranty', 'like', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['purchase_id'])) {
            $query->where('purchase_id', $filters['purchase_id']);
        }

        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): PurchaseItem
    {
        $purchaseItem = $this->model->create($data);
        log_activity('Created a new purchase item', authId(), null, 'purchase_item', 'created', [
            'id' => $purchaseItem->id, 'serial_no' => $purchaseItem->serial_no,
        ]);

        return $purchaseItem;
    }

    public function show(string $id, $relations = [], $throwException = true): PurchaseItem
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): PurchaseItem
    {
        $purchaseItem = $this->show($id);
        $purchaseItem->update($data);
        log_activity('Updated a purchase item', authId(), null, 'purchase_item', 'updated', $purchaseItem->getChanges() + ['id' => $purchaseItem->id]);

        return $purchaseItem;
    }

    public function destroy(string $id): void
    {
        $purchaseItem = $this->show($id);
        $purchaseItem->delete();
        log_activity('Deleted a purchase item ', authId(), null, 'purchase_item', 'deleted', $purchaseItem->toArray());
    }

    public function toggleStatus(string $id): PurchaseItem
    {
        $purchaseItem = $this->show($id);
        $purchaseItem->status = $purchaseItem->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $purchaseItem->save();
        log_activity('Toggled purchase item status to '.($purchaseItem->status->label()), authId(), null, 'purchase_item', 'updated', $purchaseItem->getChanges() + ['id' => $purchaseItem->id]);

        return $purchaseItem;
    }
}
