<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\EntityType\EntityTypeEnum;
use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Models\Stock;
use App\Models\StockCustomerOnu;
use App\Models\StockHistory;
use App\Models\StockOut;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OnuDetailsService
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockCustomerOnu;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['network_id']), function ($q) use ($filters) {
            $q->where('network_id', $filters['network_id']);
        })->when(isset($filters['provided_by']), function ($q) use ($filters) {
            $q->where('provided_by', $filters['provided_by']);
        })->when(isset($filters['serial_no']), function ($q) use ($filters) {
            $q->where('serial_no', $filters['serial_no']);
        })->when(isset($filters['mac_address']), function ($q) use ($filters) {
            $q->where('mac_address', $filters['mac_address']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): StockCustomerOnu
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes)
    {
        return DB::transaction(function () use ($attributes) {

            if ($attributes['provided_by'] === 'customer') {

                $customerOnu = $this->model->create([
                    'user_id' => $attributes['user_id'],
                    'network_id' => $attributes['network_id'],
                    'provided_by' => 'customer',
                    'brand' => $attributes['brand'] ?? null,
                    'serial_no' => $attributes['serial_no'] ?? null,
                    'mac_address' => $attributes['mac_address'] ?? null,
                    'remark' => $attributes['remark'] ?? null,
                ]);

                return $customerOnu;
            } elseif ($attributes['provided_by'] === 'stock') {

                $onuProductIds = explode(',', env('ONU_PRODUCT_ID', '1,2'));

                $availableStock = Stock::whereIn('stock_product_id', $onuProductIds)
                    ->where('status', StockStatusEnum::AVAILABLE->value)
                    ->where(function ($query) use ($attributes) {
                        $query->where('serial_no', $attributes['serial_no']);
                    })->first();

                if (! $availableStock) {
                    throw new \Exception('No available ONU found');
                }

                $stockOut = StockOut::create([
                    'stock_category_id' => $availableStock->stock_category_id,
                    'stock_product_id' => $availableStock->stock_product_id,
                    'network_id' => $attributes['network_id'],
                    'quantity' => $attributes['quantity'] ?? 1,
                    'brand_id' => $availableStock->brand_id,
                    'unit_id' => $availableStock->unit_id,
                    'serial_no' => $availableStock->serial_no,
                    'mac_address' => $availableStock->mac_address,
                    'product_code' => $availableStock->product_code,
                    'stock_out_date' => now(),
                    'assignable_type' => EntityTypeEnum::CLIENT->value,
                    'assignable_id' => $attributes['user_id'] ?? null,
                    'remarks' => $attributes['remarks'] ?? null,
                    'created_by' => authId(),
                ]);

                $stockHistory = StockHistory::create([
                    'product_code' => $availableStock->product_code,
                    'type' => 'out',
                    'stockable_type' => StockOut::class,
                    'stockable_id' => $stockOut->id,
                    'quantity' => $attributes['quantity'] ?? 1,
                    'unit_id' => $attributes['unit_id'] ?? null,
                    'stock_product_id' => $availableStock->stock_product_id,
                    'stock_category_id' => $availableStock->stock_category_id,
                    'requisition_id' => $attributes['requisition_id'] ?? null,
                    'vendor_id' => $attributes['vendor_id'] ?? null,
                    'serial_no' => $availableStock->serial_no,
                    'mac_address' => $availableStock->mac_address,
                    'brand_id' => $availableStock->brand_id,
                    'warranty' => $availableStock->warranty,
                    'price' => $availableStock->price,
                    'date' => now(),
                    'admin_id' => authId(),
                ]);

                $stockOut->update(['stock_history_id' => $stockHistory->id]);

                $availableStock->update([
                    'status' => StockStatusEnum::ASSIGNED->value,
                ]);

                return $stockOut->load(['stockHistory', 'stockProduct', 'brand', 'unit']);
            }

        });
    }

    public function getAvailableOnuList(): array
    {
        $onuProductIds = explode(',', env('ONU_PRODUCT_ID', '1,2'));

        return Stock::whereIn('stock_product_id', $onuProductIds)
            ->where('status', StockStatusEnum::AVAILABLE->value)
            ->with(['stockProduct', 'brand'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->stock_product_id,
                    'product_name' => $item->stockProduct->name ?? null,
                    'brand' => $item->brand->name ?? null,
                    'serial_no' => $item->serial_no,
                    'mac_address' => $item->mac_address,
                    'product_code' => $item->product_code,
                ];
            })
            ->toArray();
    }
}
