<?php

namespace App\Services\Business\Stock;

use App\Models\Business\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockMovementService
{
    /**
     * Paginated ledger of all stock movements.
     * Filters: product_id, type, reference_type, from_date, to_date, per_page
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = StockMovement::with(['product:id,name', 'unit:id,name,symbol', 'market:id,name']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }
        if (!empty($filters['reference_id'])) {
            $query->where('reference_id', $filters['reference_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        $perPage = (int) ($filters['per_page'] ?? 30);
        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Current stock balance per product (grouped by product + location type).
     * type: 1=purchase(+), 2=dispatch(-), 3=receive(+), 4=sale(-), 5=return(+), 6=damage(-), 7=adjustment(±)
     */
    public function stockBalance(array $filters = []): array
    {
        $query = StockMovement::query()
            ->selectRaw('
                product_id,
                SUM(CASE WHEN type IN (1,3,5) THEN quantity ELSE 0 END) -
                SUM(CASE WHEN type IN (2,4,6) THEN ABS(quantity) ELSE 0 END) as net_quantity,
                SUM(CASE WHEN type = 1 THEN quantity ELSE 0 END) as purchased,
                SUM(CASE WHEN type = 2 THEN ABS(quantity) ELSE 0 END) as dispatched,
                SUM(CASE WHEN type = 3 THEN quantity ELSE 0 END) as received,
                SUM(CASE WHEN type = 4 THEN ABS(quantity) ELSE 0 END) as sold,
                SUM(CASE WHEN type = 6 THEN ABS(quantity) ELSE 0 END) as damaged,
                SUM(CASE WHEN type = 5 THEN quantity ELSE 0 END) as returned
            ')
            ->groupBy('product_id')
            ->with(['product:id,name']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->get()->map(function ($row) {
            return [
                'product_id'   => $row->product_id,
                'product_name' => $row->product?->name,
                'purchased'    => (float) $row->purchased,
                'dispatched'   => (float) $row->dispatched,
                'received'     => (float) $row->received,
                'sold'         => (float) $row->sold,
                'damaged'      => (float) $row->damaged,
                'returned'     => (float) $row->returned,
                'net_balance'  => (float) $row->net_quantity,
            ];
        })->values()->toArray();
    }
}