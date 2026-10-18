<?php

namespace App\Services\Modules\Inventory;

use App\Models\StockHistory;
use Illuminate\Pagination\LengthAwarePaginator;

class StockReportService
{
    public function index(array $filters): LengthAwarePaginator
    {
        $query = StockHistory::query();

        $query->when(isset($filters['stock_category_id']), function ($q) use ($filters) {
            $q->where('stock_category_id', $filters['stock_category_id']);
        })->when(isset($filters['stock_product_id']), function ($q) use ($filters) {
            $q->where('stock_product_id', $filters['stock_product_id']);
        })->when(isset($filters['admin_id']), function ($q) use ($filters) {
            $q->where('admin_id', $filters['admin_id']);
        })->when(isset($filters['vendor_id']), function ($q) use ($filters) {
            $q->where('vendor_id', $filters['vendor_id']);
        })->when(isset($filters['brand_id']), function ($q) use ($filters) {
            $q->where('brand_id', $filters['brand_id']);
        })->when(isset($filters['type']), function ($q) use ($filters) {
            $q->where('type', $filters['type']);
        })->when(isset($filters['date_from']), function ($q) use ($filters) {
            $q->whereDate('date', '>=', $filters['date_from']);
        })->when(isset($filters['date_to']), function ($q) use ($filters) {
            $q->whereDate('date', '<=', $filters['date_to']);
        })->when(isset($filters['serial_no']), function ($q) use ($filters) {
            $q->where('serial_no', 'like', '%'.$filters['serial_no'].'%');
        })->when(isset($filters['mac_address']), function ($q) use ($filters) {
            $q->where('mac_address', 'like', '%'.$filters['mac_address'].'%');
        })->when(isset($filters['product_code']), function ($q) use ($filters) {
            $q->where('product_code', 'like', '%'.$filters['product_code'].'%');
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where(function ($sq) use ($filters) {
                $sq->where('product_code', 'like', '%'.$filters['search'].'%')
                    ->orWhere('serial_no', 'like', '%'.$filters['search'].'%')
                    ->orWhere('mac_address', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('stockProduct', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'like', '%'.$filters['search'].'%');
                    })
                    ->orWhereHas('stockCategory', function ($categoryQuery) use ($filters) {
                        $categoryQuery->where('name', 'like', '%'.$filters['search'].'%');
                    })
                    ->orWhereHas('vendor', function ($vendorQuery) use ($filters) {
                        $vendorQuery->where('first_name', 'like', '%'.$filters['search'].'%')
                            ->orWhere('last_name', 'like', '%'.$filters['search'].'%');
                    });
            });
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }
}
