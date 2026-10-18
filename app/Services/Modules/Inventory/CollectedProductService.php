<?php

namespace App\Services\Modules\Inventory;

use App\Models\CollectedProduct;
use App\Models\Stock;
use App\Models\StockHistory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CollectedProductService
{
    protected $model;

    public function __construct()
    {
        $this->model = new CollectedProduct;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model;

        $query->when(isset($filters['stock_category_id']), function ($q) use ($filters) {
            $q->where('stock_category_id', $filters['stock_category_id']);
        })->when(isset($filters['stock_product_id']), function ($q) use ($filters) {
            $q->where('stock_product_id', $filters['stock_product_id']);
        })->when(isset($filters['vendor_id']), function ($q) use ($filters) {
            $q->where('vendor_id', $filters['vendor_id']);
        })->when(isset($filters['collectable_type']), function ($q) use ($filters) {
            $q->where('collectable_type', $filters['collectable_type']);
        })->when(isset($filters['collectable_id']), function ($q) use ($filters) {
            $q->where('collectable_id', $filters['collectable_id']);
        })->when(isset($filters['collected_date_from']), function ($q) use ($filters) {
            $q->whereDate('collected_date', '>=', $filters['collected_date_from']);
        })->when(isset($filters['collected_date_to']), function ($q) use ($filters) {
            $q->whereDate('collected_date', '<=', $filters['collected_date_to']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): CollectedProduct
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): CollectedProduct
    {
        return DB::transaction(function () use ($attributes) {
            $collectedProduct = $this->model->create([
                'stock_category_id' => $attributes['stock_category_id'] ?? null,
                'stock_product_id' => $attributes['stock_product_id'] ?? null,
                'vendor_id' => $attributes['vendor_id'] ?? null,
                's_mtr' => $attributes['s_mtr'] ?? null,
                'e_mtr' => $attributes['e_mtr'] ?? null,
                'product_code' => $attributes['product_code'] ?? null,
                'quantity' => $attributes['quantity'],
                'unit_id' => $attributes['unit_id'] ?? null,
                'amount' => $attributes['amount'],
                'collected_date' => $attributes['collected_date'],
                'created_by' => authId(),
                'warranty' => $attributes['warranty'] ?? null,
                'comment' => $attributes['comment'] ?? null,
                'collectable_type' => $attributes['collectable_type'] ?? null,
                'collectable_id' => $attributes['collectable_id'],
                'brand_id' => $attributes['brand_id'] ?? null,
            ]);

            Stock::create([
                'itemable_type' => CollectedProduct::class,
                'itemable_id' => $collectedProduct->id,
                'stock_category_id' => $collectedProduct->stock_category_id,
                'stock_product_id' => $collectedProduct->stock_product_id,
                'product_code' => $collectedProduct->product_code,
                's_mtr' => $collectedProduct->s_mtr,
                'e_mtr' => $collectedProduct->e_mtr,
                'brand_id' => null,
                'warranty' => $collectedProduct->warranty,
                'stock_date' => $collectedProduct->collected_date,
                'quantity' => $collectedProduct->quantity,
                'status' => 1,
            ]);

            StockHistory::create([
                'product_code' => $collectedProduct->product_code,
                'type' => 'collected_in',
                'stockable_type' => CollectedProduct::class,
                'stockable_id' => $collectedProduct->id,
                's_mtr' => $collectedProduct->s_mtr,
                'e_mtr' => $collectedProduct->e_mtr,
                'quantity' => $collectedProduct->quantity,
                'unit_id' => $collectedProduct->unit_id,
                'stock_product_id' => $collectedProduct->stock_product_id,
                'stock_category_id' => $collectedProduct->stock_category_id,
                'vendor_id' => $collectedProduct->vendor_id,
                'serial_no' => null,
                'mac_address' => null,
                'brand_id' => $collectedProduct->brand_id,
                'warranty' => $collectedProduct['warranty'] ?? null,
                'price' => $collectedProduct['unit_price'],
                'date' => now(),
                'admin_id' => authId(),
            ]);

            log_activity('Created a new collected product', authId(), null, 'collected_product', 'created', ['id' => $collectedProduct->id, 'product_code' => $collectedProduct->product_code, 'quantity' => $collectedProduct->quantity, 'collected_date' => $collectedProduct->collected_date]);

            return $collectedProduct;
        });
    }
}
