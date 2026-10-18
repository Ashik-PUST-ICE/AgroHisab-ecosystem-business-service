<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\EntityType\EntityTypeEnum;
use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockOut;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TjBoxService
{
    protected $model;

    public function __construct()
    {
        $this->model = new StockOut;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['network_id']), function ($q) use ($filters) {
            $q->where('network_id', $filters['network_id']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $attributes)
    {
        return DB::transaction(function () use ($attributes) {
            $tjBoxProductIds = explode(',', env('TJ_BOX_PRODUCT_ID', '3,4'));

            $availableStock = Stock::whereIn('stock_product_id', $tjBoxProductIds)
                ->where('status', StockStatusEnum::AVAILABLE->value)
                ->where('quantity', '>', 0)
                ->where('stock_product_id', $attributes['stock_product_id'] ?? null)
                ->first();

            if (! $availableStock) {
                throw new \Exception('No available TJ Box stock found for the given product.');
            }

            $stockOut = StockOut::create([
                'stock_category_id' => $availableStock->stock_category_id,
                'network_id' => $attributes['network_id'] ?? null,
                'quantity' => $attributes['quantity'] ?? 1,
                'unit_id' => $availableStock->unit_id,
                'serial_no' => $availableStock->serial_no,
                'mac_address' => $availableStock->mac_address,
                'product_code' => $availableStock->product_code,
                'stock_out_date' => now(),
                'assignable_type' => EntityTypeEnum::CLIENT->value,
                'assignable_id' => $attributes['user_id'] ?? null,
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

            $availableStock->decrement('quantity', 1);

            $tjBox = $this->model->create([

                'stock_product_id' => $availableStock->stock_product_id,
                'brand_id' => $attributes['brand_id'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
            ]);

            log_activity('Created a new TJ Box', authId(), null, 'stock_customer_tj_boxes', 'created', [
                'id' => $tjBox->id,
                'brand_id' => $tjBox->brand_id,
                'product_id' => $tjBox->product_id,
            ]);

            return $tjBox;
        });
    }

    public function getAvailableList(): array
    {
        $tjBoxProductIds = explode(',', env('TJ_BOX_PRODUCT_ID', '3,4'));

        return Stock::whereIn('stock_product_id', $tjBoxProductIds)
            ->where('status', StockStatusEnum::AVAILABLE->value)
            ->where('quantity', '>', 0)
            ->with(['stockProduct', 'brand'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->stock_product_id,
                    'product_name' => $item->stockProduct->name ?? null,
                    'brand' => $item->brand->name ?? null,
                    'quantity' => $item->quantity,
                    'product_code' => $item->product_code,
                ];
            })
            ->toArray();
    }
}
