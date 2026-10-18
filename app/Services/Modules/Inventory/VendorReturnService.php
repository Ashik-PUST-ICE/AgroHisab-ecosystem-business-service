<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Enums\Commons\TransactionType\TransactionTypeEnum;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\VendorReturn;
use Illuminate\Support\Facades\DB;

class VendorReturnService
{
    protected VendorReturn $model;

    public function __construct()
    {
        $this->model = new VendorReturn;
    }

    public function index(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('return_type', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('stockProduct', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'like', '%'.$filters['search'].'%');
                    })
                    ->orWhereHas('vendor', function ($vendorQuery) use ($filters) {
                        $vendorQuery->where('first_name', 'like', '%'.$filters['search'].'%')
                            ->orWhere('last_name', 'like', '%'.$filters['search'].'%');
                    });
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $attributes): VendorReturn
    {
        try {
            DB::beginTransaction();

            $return = VendorReturn::create([
                'stock_product_id' => $attributes['stock_product_id'],
                'stock_category_id' => $attributes['stock_category_id'],
                'quantity' => $attributes['quantity'],
                'vendor_id' => $attributes['vendor_id'],
                'brand_id' => $attributes['brand_id'] ?? null,
                'unit_id' => $attributes['unit_id'],
                'return_type' => $attributes['return_type'],
                'account_id' => $attributes['account_id'] ?? null,
                'amount' => $attributes['amount'] ?? 0,
                's_mtr' => $attributes['s_mtr'] ?? null,
                'e_mtr' => $attributes['e_mtr'] ?? null,
                'replace_product_id' => $attributes['replace_product_id'] ?? null,
                'created_by' => authId(),
            ]);

            if ($attributes['return_type'] == 'Amount') {
                Transaction::create([
                    'branch_id' => $return->branch_id,
                    'type' => TransactionTypeEnum::CREDIT,
                    'account_id' => $return->account_id,
                    'description' => 'Cash Return Received from Vendor('.optional($return->vendor)->name.') for return '.optional($return->product)->name,
                    'amount' => $return->amount,
                    'transaction_date' => $return->return_date ?? now(),
                    'created_by' => authId(),
                    'transactionable_type' => VendorReturn::class,
                    'transactionable_id' => $return->id,
                ]);
            } else {

                $requisition = Requisition::create([
                    'product_type' => $attributes['stock_category_id'] == 2 ? 'Fiber' : 'Instrument',
                    'purpose' => 'Stock Vendor Return',
                    'stock_product_id' => $attributes['replace_product_id'],
                    'vendor_id' => $attributes['vendor_id'],
                    'admin_id' => authId(),
                    'request_by' => authId(),
                    'quantity' => $attributes['quantity'],
                    'unit' => $attributes['unit'] ?? 'Piece',
                    'remarks' => 'Auto Generated From Stock Vendor Return',
                    'status' => 'Approved',
                    'final_status' => 'Approved',
                    'first_approved_by' => authId(),
                    'final_approved_by' => authId(),
                    'stock_vendor_return_id' => $return->id,
                ]);

                if (isset($attributes['fiber_code']) && $attributes['stock_category_id'] == 2) {
                    $history = StockHistory::getStockBySerial($attributes['stock_product_id'], $attributes['fiber_code']);

                    if (isset($history)) {
                        if ($history->total_length < $attributes['quantity']) {
                            throw new \Exception('Stock Not Available');
                        }

                        StockHistory::create([
                            'type' => 'Stock Vendor Return',
                            'product_code' => $history->product_code,
                            'stockable_id' => $return->id,
                            'stockable_type' => VendorReturn::class,
                            's_mtr' => $attributes['s_mtr'] ?? null,
                            'e_mtr' => $attributes['e_mtr'] ?? null,
                            'quantity' => $attributes['quantity'],
                            'unit' => $history->unit,
                            'stock_product_id' => $attributes['stock_product_id'],
                            'stock_category_id' => $attributes['stock_category_id'],
                            'serial_no' => $history->serial_no ?? null,
                            'mac_address' => $history->mac_address,
                            'brand' => $history->brand,
                            'warranty' => $history->warranty,
                            'date' => now()->format('Y-m-d H:i:s'),
                            'available' => StockStatusEnum::VENDOR_RETURN->value,
                            'admin_id' => authId(),
                            'pre_product_id' => $attributes['stock_product_id'],
                            'stock_requisition_id' => $requisition->id,
                            'vendor_id' => $history->vendor_id,
                            'price' => $history->price,
                        ]);
                    } else {
                        throw new \Exception('Product Code Not Found');
                    }
                } elseif (isset($attributes['serial_no']) && is_array($attributes['serial_no']) && $attributes['stock_category_id'] != 2) {
                    foreach ($attributes['serial_no'] as $key => $serial_no) {
                        $history = StockHistory::where('stock_product_id', $attributes['stock_product_id'])
                            ->where('serial_no', $serial_no)
                            ->where('available', 1)
                            ->latest()
                            ->first();

                        if (isset($history)) {
                            StockHistory::create([
                                'type' => 'Stock Vendor Return',
                                'product_code' => $history->product_code,
                                'stockable_id' => $return->id,
                                'stockable_type' => VendorReturn::class,
                                'quantity' => 1,
                                'unit' => $history->unit,
                                'stock_product_id' => $attributes['stock_product_id'],
                                'stock_category_id' => $attributes['stock_category_id'],
                                'serial_no' => $serial_no ?? null,
                                'mac_address' => $history->mac_address,
                                'brand' => $history->brand,
                                'warranty' => $history->warranty,
                                'date' => now()->format('Y-m-d H:i:s'),
                                'available' => StockStatusEnum::VENDOR_RETURN->value, // Status = 5
                                'admin_id' => authId(),
                                'pre_product_id' => $attributes['stock_product_id'],
                                'stock_requisition_id' => $requisition->id,
                                'vendor_id' => $history->vendor_id,
                                'price' => $history->price,
                            ]);

                            $history->update([
                                'available' => StockStatusEnum::VENDOR_RETURN->value,
                            ]);
                        } else {
                            throw new \Exception('Serial No Not Found');
                        }
                    }
                } else {
                    $product = Product::where('id', $attributes['stock_product_id'])->first();

                    if ($product->getAvailableStock() < $attributes['quantity']) {
                        throw new \Exception('Stock Not Available');
                    }

                    $history = StockHistory::where('stock_product_id', $attributes['stock_product_id'])->latest()->first();

                    if (isset($history)) {
                        StockHistory::create([
                            'type' => 'Stock Vendor Return',
                            'product_code' => $history->product_code,
                            'stockable_id' => $return->id,
                            'stockable_type' => VendorReturn::class,
                            'quantity' => $attributes['quantity'],
                            'unit' => $history->unit,
                            'stock_product_id' => $attributes['stock_product_id'],
                            'stock_category_id' => $attributes['stock_category_id'],
                            'serial_no' => $history->serial_no ?? null,
                            'mac_address' => $history->mac_address,
                            'brand' => $history->brand,
                            'warranty' => $history->warranty,
                            'date' => now()->format('Y-m-d H:i:s'),
                            'available' => StockStatusEnum::VENDOR_RETURN->value,
                            'admin_id' => authId(),
                            'pre_product_id' => $attributes['stock_product_id'],
                            'stock_requisition_id' => $requisition->id,
                            'vendor_id' => $history->vendor_id,
                            'price' => $history->price,
                        ]);
                    } else {
                        throw new \Exception('Product Not Found');
                    }
                }

                $replaceHistory = StockHistory::where('stock_product_id', $attributes['replace_product_id'])->latest()->first();

                StockHistory::create([
                    'type' => 'Requisition Received',
                    'product_code' => $replaceHistory->product_code ?? null,
                    'stockable_id' => $requisition->id,
                    'stockable_type' => Requisition::class,
                    'quantity' => $attributes['quantity'],
                    'unit' => $attributes['unit'] ?? 'Piece',
                    'stock_product_id' => $attributes['replace_product_id'],
                    'stock_category_id' => $attributes['stock_category_id'],
                    'serial_no' => null,
                    'mac_address' => $replaceHistory->mac_address ?? null,
                    'brand' => $replaceHistory->brand ?? null,
                    'warranty' => $replaceHistory->warranty ?? null,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'available' => StockStatusEnum::AVAILABLE->value,
                    'admin_id' => authId(),
                    'pre_product_id' => $attributes['stock_product_id'],
                    'stock_requisition_id' => $requisition->id,
                    'vendor_id' => $attributes['vendor_id'],
                    'price' => $replaceHistory->price ?? 0,
                ]);
            }

            DB::commit();

            log_activity('Created a new vendor return', authId(), null, 'vendor_return', 'created', [
                'id' => $return->id,
                'return_type' => $return->return_type,
                'quantity' => $return->quantity,
            ]);

            return $return;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(string $id, $relations = [], $throwException = true): VendorReturn
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }
}
