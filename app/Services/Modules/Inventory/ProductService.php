<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    protected Product $model;

    public function __construct()
    {
        $this->model = new Product;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['stockCategory', 'unit']);

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('slug', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Product
    {

        $product = $this->model->create($data);
        log_activity(
            'Created a new product',
            authId(),
            null,
            'product',
            'created',
            [
                'id' => $product->id,
                'name' => $product->name,
            ]
        );

        return $product;
    }

    public function show(string $id, $relations = [], $throwException = true): Product
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Product
    {
        $product = $this->show($id);
        $product->update($data);
        log_activity('Updated a product', authId(), null, 'product', 'updated', $product->getChanges() + ['id' => $product->id]);

        return $product;
    }

    public function destroy(string $id): void
    {
        $product = $this->show($id);
        $product->delete();
        log_activity('Deleted a product ', authId(), null, 'product', 'deleted', $product->toArray());
    }

    public function toggleStatus(string $id): Product
    {
        $product = $this->show($id);
        $product->status = $product->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $product->save();
        log_activity('Toggled product status to ' . ($product->status->label()), authId(), null, 'product', 'updated', $product->getChanges() + ['id' => $product->id]);

        return $product;
    }

    public function list(array $filters = []): array
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%');
        });

        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        }, function ($q) {
            $q->where('status', StatusEnum::ACTIVE);
        });

        $query->when(isset($filters['category']), function ($q) use ($filters) {
            $q->where('stock_category_id', $filters['category']);
        });

        $brands = $query->select('id', 'name', 'status')->orderBy('name')->get();

        return $brands->map(function ($brand) {
            return [
                'value' => $brand->id,
                'label' => $brand->name,
            ];
        })->toArray();
    }

    public function getAvailableList(string $productId): array
    {
        $product = $this->show($productId, [], false);
        if (!$product) {
            return [];
        }

        return $product->availableStocks()->with('brand')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'serial_no' => $item->serial_number,
                'mac_address' => $item->mac_address,
                'quantity' => $item->quantity,
                'brand' => $item->brand ? $item->brand?->name : null,
            ];
        })->toArray();
    }
}
