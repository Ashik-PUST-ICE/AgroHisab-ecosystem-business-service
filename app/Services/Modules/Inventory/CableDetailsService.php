<?php

namespace App\Services\Modules\Inventory;

use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;

class CableDetailsService
{
    public function getFiberProducts()
    {
        $fiberCategoryIds = explode(',', env('FIBER_CATEGORY_ID', '7,8'));

        return Stock::with(['stockCategory', 'stockProduct', 'brand'])
            ->whereIn('stock_category_id', $fiberCategoryIds)
            ->where('quantity', '>', 0)
            ->select('id', 'stock_category_id', 'stock_product_id', 'product_code', 'brand_id', 'quantity')
            ->get()
            ->groupBy('stock_product_id')
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'product_id' => $first->stock_product_id,
                    'product_name' => $first->stockProduct->name ?? 'N/A',
                    'category' => $first->stockCategory->name ?? 'N/A',
                    'brand' => $first->brand->name ?? 'N/A',
                    'total_quantity' => $group->sum('quantity'),
                ];
            })
            ->values();
    }

    public function getProductDetails($productId)
    {
        $stocks = Stock::where('stock_product_id', $productId)
            ->where('quantity', '>', 0)
            ->with(['stockCategory', 'stockProduct', 'brand', 'unit'])
            ->get()
            ->groupBy('product_code');

        if ($stocks->isEmpty()) {
            throw new \Exception('No available stock found for this product.');
        }

        return $stocks->map(function ($group, $productCode) {
            return [
                'product_code' => $productCode,
                'stocks' => $group->map(function ($stock) {
                    return [
                        'stock_id' => $stock->id,
                        's_meter' => $stock->s_mtr,
                        'e_meter' => $stock->e_mtr,
                        'quantity' => $stock->e_mtr - $stock->s_mtr,

                    ];
                })->values(),
            ];
        })->values();
    }

    public function store(array $data): StockHistory
    {
        return DB::transaction(function () use ($data) {

            $length = $data['e_meter'] - $data['s_meter'];

            if ($length <= 0) {
                throw new \Exception('End meter must be greater than start meter.');
            }

            $stock = Stock::where('stock_product_id', $data['product_id'])
                ->where('product_code', $data['product_code'])
                ->where('quantity', '>', 0)
                ->first();

            if (! $stock) {
                throw new \Exception("Stock not found for product ID: {$data['product_id']} and product code: {$data['product_code']}. Please verify the product exists and has available quantity.");
            }

            $availableLength = $stock->e_mtr - $stock->s_mtr;
            if ($length > $availableLength) {
                throw new \Exception('Requested length exceeds available stock.');
            }

            // Create StockOut entry first
            $stockOut = StockOut::create([
                'stock_product_id' => $data['product_id'],
                'stock_category_id' => $stock->stock_category_id,
                'product_code' => $data['product_code'],
                'quantity' => $length,
                'unit_id' => $stock->unit_id,
                's_mtr' => $data['s_meter'],
                'e_mtr' => $data['e_meter'],
                'stock_out_date' => now(),
                'remarks' => 'Cable installation - Fiber cable used',
                'created_by' => authId(),
            ]);

            $stockHistory = StockHistory::create([
                'product_code' => $data['product_code'],
                'type' => 'stock_out',
                'stockable_type' => StockOut::class,
                'stockable_id' => $stockOut->id,
                's_mtr' => $data['s_meter'],
                'e_mtr' => $data['e_meter'],
                'quantity' => $length,
                'unit_id' => $stock->unit_id,
                'stock_product_id' => $data['product_id'],
                'stock_category_id' => $stock->stock_category_id,
                'brand_id' => $stock->brand_id,
                'date' => now(),
                'admin_id' => authId(),
            ]);

            $stock->update([
                's_mtr' => $data['e_meter'],
                'quantity' => $stock->quantity - $length,
            ]);

            log_activity('Cable details saved', authId(), null, 'stock_history', 'created', ['id' => $stockHistory->id, 'product_code' => $stockHistory->product_code]);

            return $stockHistory;
        });
    }
}
