<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockTransfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    protected StockTransfer $model;

    public function __construct()
    {
        $this->model = new StockTransfer;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('serial_no', 'like', '%'.$filters['search'].'%')
                    ->orWhere('mac_address', 'like', '%'.$filters['search'].'%')
                    ->orWhere('product_code', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('stockProduct', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'like', '%'.$filters['search'].'%');
                    });
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {

            $serialNo = $data['serial_no'] ?? null;
            $macAddress = $data['mac_address'] ?? null;
            $quantity = $data['quantity'] ?? 1;
            $hasIdentifier = $serialNo || $macAddress;

            $identifierQuery = fn ($query) => $query
                ->when($serialNo, fn ($q) => $q->where('serial_no', $serialNo))
                ->when($macAddress, fn ($q) => $q->orWhere('mac_address', $macAddress));

            if ($hasIdentifier && StockTransfer::where($identifierQuery)->exists()) {
                throw new \Exception('This item has already been transferred.');
            }

            $sourceStock = $hasIdentifier
                ? Stock::where('status', StockStatusEnum::AVAILABLE->value)
                    ->where('stock_product_id', $data['stock_product_id'])
                    ->where($identifierQuery)
                    ->first()
                : Stock::where('status', StockStatusEnum::AVAILABLE->value)
                    ->where('stock_product_id', $data['stock_product_id'])
                    ->where('product_code', $data['product_code'])
                    ->whereNull('serial_no')
                    ->whereNull('mac_address')
                    ->first();

            if (! $sourceStock) {
                throw new \Exception('No available stock found for the source product.');
            }

            if (! $hasIdentifier && $sourceStock->quantity < $quantity) {
                throw new \Exception('Insufficient stock quantity. Available: '.$sourceStock->quantity);
            }

            $productCode = $data['product_code'] ?? $sourceStock->product_code ?? null;

            $stockTransfer = $this->model->create([
                'stock_category_id' => $data['stock_category_id'] ?? null,
                'stock_product_id' => $data['stock_product_id'],
                'transfer_product_id' => $data['transfer_product_id'],
                's_mtr' => $data['s_mtr'] ?? null,
                'e_mtr' => $data['e_mtr'] ?? null,
                'quantity' => $quantity,
                'unit_id' => $data['unit_id'] ?? null,
                'product_code' => $productCode,
                'serial_no' => $serialNo,
                'mac_address' => $macAddress,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => authId(),
            ]);

            $createStockHistory = function ($type, $stockProductId) use ($data, $stockTransfer, $sourceStock, $quantity) {
                StockHistory::create([
                    'product_code' => $data['product_code'] ?? null,
                    'type' => $type,
                    'stockable_type' => StockTransfer::class,
                    'stockable_id' => $stockTransfer->id,
                    's_mtr' => $data['s_mtr'] ?? null,
                    'e_mtr' => $data['e_mtr'] ?? null,
                    'quantity' => $quantity,
                    'unit_id' => $data['unit_id'] ?? null,
                    'stock_product_id' => $stockProductId,
                    'stock_category_id' => $data['stock_category_id'] ?? null,
                    'serial_no' => $data['serial_no'] ?? null,
                    'mac_address' => $data['mac_address'] ?? null,
                    'brand_id' => $sourceStock->brand_id ?? null,
                    'warranty' => $sourceStock->warranty ?? null,
                    'price' => $data['unit_price'] ?? null,
                    'date' => now(),
                    'admin_id' => authId(),
                ]);
            };

            $createStockHistory('transfer_out', $data['stock_product_id']);
            $createStockHistory('transfer_add', $data['transfer_product_id']);

            if ($hasIdentifier) {
                $sourceStock->update([
                    'status' => StockStatusEnum::TRANSFERRED->value,
                ]);
            } else {
                $newQty = $sourceStock->quantity - $quantity;
                $newQty <= 0 ? $sourceStock->delete() : $sourceStock->update(['quantity' => $newQty]);
            }

            if ($hasIdentifier) {
                Stock::create([
                    'stock_category_id' => $data['stock_category_id'] ?? null,
                    'stock_product_id' => $data['transfer_product_id'],
                    'itemable_type' => StockTransfer::class,
                    'itemable_id' => $stockTransfer->id,
                    'serial_no' => $serialNo,
                    'mac_address' => $macAddress,
                    'product_code' => $data['product_code'] ?? null,
                    's_mtr' => $data['s_mtr'] ?? null,
                    'e_mtr' => $data['e_mtr'] ?? null,
                    'brand_id' => $sourceStock->brand_id ?? null,
                    'warranty' => $sourceStock->warranty ?? null,
                    'stock_date' => now(),
                    'quantity' => 1,
                    'status' => StockStatusEnum::AVAILABLE->value,
                ]);
            } else {
                $targetStock = Stock::where('stock_product_id', $data['transfer_product_id'])
                    ->where('product_code', $data['product_code'])
                    ->whereNull('serial_no')
                    ->whereNull('mac_address')
                    ->first();

                $targetStock
                    ? $targetStock->increment('quantity', $quantity)
                    : Stock::create([
                        'stock_category_id' => $data['stock_category_id'] ?? null,
                        'stock_product_id' => $data['transfer_product_id'],
                        'itemable_type' => StockTransfer::class,
                        'itemable_id' => $stockTransfer->id,
                        'product_code' => $data['product_code'] ?? null,
                        's_mtr' => $data['s_mtr'] ?? null,
                        'e_mtr' => $data['e_mtr'] ?? null,
                        'brand_id' => $sourceStock->brand_id ?? null,
                        'warranty' => $sourceStock->warranty ?? null,
                        'stock_date' => now(),
                        'quantity' => $quantity,
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);
            }

            log_activity('Created a new stock transfer', authId(), null, 'stock_transfer', 'created', ['id' => $stockTransfer->id, 'product_code' => $stockTransfer->product_code, 'serial_no' => $stockTransfer->serial_no]);

            return $stockTransfer;
        });
    }

    public function show(string $id, $relations = [], $throwException = true): StockTransfer
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function getAvailableSerials(int $productId): array
    {
        return Stock::where('stock_product_id', $productId)
            ->where('status', StockStatusEnum::AVAILABLE->value)
            ->whereNotNull('serial_no')
            ->select('serial_no', 'mac_address')
            ->get()
            ->map(function ($stock) {
                return [
                    'serial_no' => $stock->serial_no,
                    'mac_address' => $stock->mac_address,
                    'display' => $stock->serial_no.($stock->mac_address ? ' ('.$stock->mac_address.')' : ''),
                ];
            })
            ->toArray();
    }
}
