<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockOut;
use App\Models\StockReturn;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockReturnService
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockReturn;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->model
            ->when($filters['stock_category_id'] ?? null, fn ($q, $v) => $q->where('stock_category_id', $v))
            ->when($filters['stock_product_id'] ?? null, fn ($q, $v) => $q->where('stock_product_id', $v))
            ->when($filters['return_product_id'] ?? null, fn ($q, $v) => $q->where('return_product_id', $v))
            ->when($filters['returnable_type'] ?? null, fn ($q, $v) => $q->where('returnable_type', $v))
            ->when($filters['returnable_id'] ?? null, fn ($q, $v) => $q->where('returnable_id', $v))
            ->when($filters['return_type'] ?? null, fn ($q, $v) => $q->where('return_type', $v))
            ->when($filters['return_date_from'] ?? null, fn ($q, $v) => $q->whereDate('return_date', '>=', $v))
            ->when($filters['return_date_to'] ?? null, fn ($q, $v) => $q->whereDate('return_date', '<=', $v))
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): StockReturn
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function getAvailableProducts(array $filters): LengthAwarePaginator
    {
        $query = Stock::query()
            ->where('status', StockStatusEnum::AVAILABLE->value)
            ->when($filters['stock_category_id'] ?? null, fn ($q, $v) => $q->where('stock_category_id', $v))
            ->when($filters['stock_product_id'] ?? null, fn ($q, $v) => $q->where('stock_product_id', $v))
            ->when($filters['product_code'] ?? null, fn ($q, $v) => $q->where('product_code', 'LIKE', "%{$v}%"))
            ->when($filters['serial_no'] ?? null, fn ($q, $v) => $q->where('serial_no', 'LIKE', "%{$v}%"))
            ->when($filters['mac_address'] ?? null, fn ($q, $v) => $q->where('mac_address', 'LIKE', "%{$v}%"))
            ->where('quantity', '>', 0);

        return $query->with(['stockProduct', 'stockCategory', 'unit'])
            ->latest('stock_date')
            ->paginate($filters['per_page'] ?? 10);
    }

    public function getAssignedProductsForReturn(array $filters): LengthAwarePaginator
    {
        $query = StockOut::query()
            ->when($filters['returnable_type'] ?? null, fn ($q, $v) => $q->where('assignable_type', $v))
            ->when($filters['returnable_id'] ?? null, fn ($q, $v) => $q->where('assignable_id', $v))
            ->when($filters['stock_product_id'] ?? null, fn ($q, $v) => $q->where('stock_product_id', $v))
            ->when($filters['stock_category_id'] ?? null, fn ($q, $v) => $q->where('stock_category_id', $v))
            ->when($filters['product_code'] ?? null, fn ($q, $v) => $q->where('product_code', 'LIKE', "%{$v}%"))
            ->when($filters['serial_no'] ?? null, fn ($q, $v) => $q->where('serial_no', 'LIKE', "%{$v}%"))
            ->when($filters['mac_address'] ?? null, fn ($q, $v) => $q->where('mac_address', 'LIKE', "%{$v}%"))
            ->when(
                isset($filters['is_returned']),
                fn ($q) => $q->where('is_returned', $filters['is_returned']),
                fn ($q) => $q->where('is_returned', false)
            );

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): StockReturn
    {
        return DB::transaction(function () use ($data) {

            if (isset($data['returnable_type'], $data['returnable_id'])) {
                $stockOutQuery = StockOut::where('assignable_type', $data['returnable_type'])
                    ->where('assignable_id', $data['returnable_id'])
                    ->where('stock_product_id', $data['stock_product_id'])
                    ->where('is_returned', false);

                // Only check serial_no if provided
                if (isset($data['serial_no'])) {
                    $stockOutQuery->where('serial_no', $data['serial_no']);
                }

                $stockOut = $stockOutQuery->first();

                if (! $stockOut) {
                    throw new \Exception('This product is not assigned to the specified returnable or already returned.');
                }
            }

            $stockReturn = $this->model->create([
                'stock_category_id' => $data['stock_category_id'] ?? null,
                'stock_product_id' => $data['stock_product_id'] ?? null,
                'return_product_id' => $data['return_product_id'] ?? null,
                's_mtr' => $data['s_mtr'] ?? null,
                'e_mtr' => $data['e_mtr'] ?? null,
                'quantity' => $data['quantity'] ?? 1,
                'unit_id' => $data['unit_id'] ?? null,
                'product_code' => $data['product_code'] ?? null,
                'serial_no' => $data['serial_no'] ?? null,
                'mac_address' => $data['mac_address'] ?? null,
                'created_by' => authId(),
                'returnable_type' => $data['returnable_type'] ?? null,
                'returnable_id' => $data['returnable_id'] ?? null,
                'return_type' => $data['return_type'] ?? null,
                'reason' => $data['reason'] ?? null,
                'return_date' => $data['return_date'] ?? now(),
            ]);

            $stockData = [
                'product_code' => $stockReturn->product_code,
                'stock_category_id' => $stockReturn->stock_category_id,
                'stock_product_id' => $stockReturn->return_product_id ?: $stockReturn->stock_product_id,
                'serial_no' => $stockReturn->serial_no,
                'mac_address' => $stockReturn->mac_address,
            ];

            $existingStock = Stock::where($stockData)->first();

            if ($existingStock) {
                $existingStock->increment('quantity', $stockReturn->quantity);
                $existingStock->update([
                    'stock_date' => now(),
                    'status' => StockStatusEnum::AVAILABLE->value,
                ]);
            } else {
                Stock::create(array_merge($stockData, [

                    's_mtr' => $stockReturn->s_mtr,
                    'e_mtr' => $stockReturn->e_mtr,
                    'stock_date' => now(),
                    'quantity' => $stockReturn->quantity,
                    'status' => StockStatusEnum::AVAILABLE->value,
                ]));
            }

            if ($stockReturn->returnable_type && $stockReturn->returnable_id) {
                $stockOutQuery = StockOut::where('assignable_type', $stockReturn->returnable_type)
                    ->where('assignable_id', $stockReturn->returnable_id)
                    ->where('stock_product_id', $stockReturn->stock_product_id)
                    ->where('is_returned', false);

                // Only check serial_no if provided
                if ($stockReturn->serial_no) {
                    $stockOutQuery->where('serial_no', $stockReturn->serial_no);
                }

                $stockOutQuery->update(['is_returned' => true]);
            }

            StockHistory::create([
                'product_code' => $stockReturn->product_code,
                'type' => 'return_in',
                'stockable_type' => StockReturn::class,
                'stockable_id' => $stockReturn->id,
                's_mtr' => $stockReturn->s_mtr,
                'e_mtr' => $stockReturn->e_mtr,
                'quantity' => $stockReturn->quantity,
                'unit_id' => $stockReturn->unit_id,
                'stock_product_id' => $stockReturn->return_product_id ?: $stockReturn->stock_product_id,
                'stock_category_id' => $stockReturn->stock_category_id,
                'serial_no' => $stockReturn->serial_no,
                'mac_address' => $stockReturn->mac_address,
                'date' => now(),
                'admin_id' => authId(),
            ]);

            log_activity(
                'Created a new stock return',
                authId(),
                null,
                'stock_return',
                'created',
                ['id' => $stockReturn->id, 'product_code' => $stockReturn->product_code, 'serial_no' => $stockReturn->serial_no]
            );

            return $stockReturn;
        });
    }
}
