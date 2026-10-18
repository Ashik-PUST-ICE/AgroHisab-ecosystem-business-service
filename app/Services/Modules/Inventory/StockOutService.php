<?php

namespace App\Services\Modules\Inventory;

use App\Models\Stock;
use App\Models\Product;
use App\Models\StockOut;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Commons\EntityType\EntityTypeEnum;
use App\Enums\Commons\StockStatus\StockStatusEnum;

class StockOutService
{
    protected StockOut $model;

    public function __construct()
    {
        $this->model = new StockOut;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('serial_no', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('mac_address', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('product_code', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('stockProduct', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): StockOut
    {
        return DB::transaction(function () use ($data) {

            $product = isset($data['stock_product_id'])
                ? Product::with('stockCategory')->find($data['stock_product_id'])
                : null;
            $isFiberProduct = $product && strtolower($product->stockCategory->name ?? '') === 'fiber';

            $serialNo = $data['serial_no'] ?? null;
            $macAddress = $data['mac_address'] ?? null;
            $hasIdentifier = $serialNo || $macAddress;

            if ($hasIdentifier) {
                $alreadyAssigned = Stock::where('status', StockStatusEnum::ASSIGNED->value)
                    ->where(function ($query) use ($serialNo, $macAddress) {
                        $query->when($serialNo, fn($q) => $q->where('serial_no', $serialNo))
                            ->when($macAddress, fn($q) => $q->where('mac_address', $macAddress));
                    })
                    ->exists();

                if ($alreadyAssigned) {
                    throw new \Exception('This item is already assigned to another user.');
                }
            }

            $availableStock = null;

            if ($hasIdentifier) {
                // Find stock by serial_no or mac_address
                $availableStock = Stock::where('status', StockStatusEnum::AVAILABLE->value)
                    ->where(function ($query) use ($serialNo, $macAddress) {
                        $query->when($serialNo, fn($q) => $q->where('serial_no', $serialNo))
                            ->when($macAddress, fn($q) => $q->where('mac_address', $macAddress));
                    })
                    ->first();
            } else {
                // Find stock without serial/mac (like TJ Box, Fiber cable)
                $availableStock = Stock::where('status', StockStatusEnum::AVAILABLE->value)
                    ->where('stock_category_id', $data['stock_category_id'])
                    ->where('stock_product_id', $data['stock_product_id'] ?? null)
                    ->where('quantity', '>', 0)
                    ->first();
            }

            if (! $availableStock) {
                throw new \Exception('No available stock found for the given product/serial.');
            }

            if ($isFiberProduct) {
                if (empty($data['s_mtr']) || empty($data['e_mtr'])) {
                    throw new \Exception('Start meter (s_mtr) and End meter (e_mtr) are required for fiber products.');
                }
            }

            $stockOut = $this->model->create([
                'stock_category_id' => $data['stock_category_id'],
                'stock_product_id' => $data['stock_product_id'] ?? null,
                'stock_history_id' => $data['stock_history_id'] ?? null,
                'network_id' => $data['network_id'] ?? null,
                's_mtr' => $data['s_mtr'] ?? null,
                'e_mtr' => $data['e_mtr'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'color' => $data['color'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'serial_no' => $serialNo ?? $availableStock->serial_no,
                'mac_address' => $macAddress ?? $availableStock->mac_address,
                'product_code' => $availableStock->product_code,
                'stock_out_date' => now(),
                'assignable_type' => $data['assignable_type'] ?? null,
                'assignable_id' => $data['assignable_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_returned' => $data['is_returned'] ?? false,
                'created_by' => authId(),
            ]);

            StockHistory::create([
                'product_code' => $availableStock->product_code,
                'type' => 'out',
                'stockable_type' => StockOut::class,
                'stockable_id' => $stockOut->id,
                's_mtr' => $data['s_mtr'] ?? null,
                'e_mtr' => $data['e_mtr'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'unit_id' => $data['unit_id'] ?? null,
                'stock_product_id' => $data['stock_product_id'],
                'stock_category_id' => $data['stock_category_id'],
                'requisition_id' => $data['requisition_id'] ?? null,
                'vendor_id' => $data['vendor_id'] ?? null,
                'serial_no' => $serialNo ?? $availableStock->serial_no,
                'mac_address' => $macAddress ?? $availableStock->mac_address,
                'brand_id' => $data['brand_id'] ?? null,
                'warranty' => $data['warranty'] ?? null,
                'price' => $data['unit_price'] ?? null,
                'date' => now(),
                'admin_id' => authId(),
            ]);

            $availableStock->update([
                'status' => StockStatusEnum::ASSIGNED->value,
            ]);

            log_activity('Created a new stock out', authId(), null, 'stock_out', 'created', ['id' => $stockOut->id, 'product_code' => $stockOut->product_code, 'serial_no' => $stockOut->serial_no]);

            return $stockOut;
        });
    }

    public function show(string $id, $relations = [], $throwException = true): StockOut
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function stockHistory(string $networkId): Collection
    {
        $stockHistory = StockOut::with(['stockProduct', 'brand'])
            ->where('assignable_type', EntityTypeEnum::CLIENT->value)
            ->where('network_id', $networkId)->get();
        return $stockHistory;
    }
}
