<?php

namespace App\Services\Modules\Inventory;

use App\Models\BandwidthPurchase;
use Illuminate\Support\Facades\DB;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Enums\Commons\RequisitionStatus\RequisitionStatusEnum;
use App\Models\Transaction;

class BandwidthPurchaseService
{
    protected BandwidthPurchase $model;

    public function __construct()
    {
        $this->model = new BandwidthPurchase;
    }


    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $filterableFields = [

            'search' => function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('link', 'like', "%{$value}%")
                        ->orWhereHas('vendor', function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        })
                        ->orWhereHas('account', function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        });
                });
            },
            'account_id' => fn($query, $value) => $query->where('account_id', $value),
            'vendor_id' => fn($query, $value) => $query->where('vendor_id', $value),
            'brand_id' => fn($query, $value) => $query->where('brand_id', $value),
            'unit_id' => fn($query, $value) => $query->where('unit_id', $value),
            'from_date' => fn($query, $value) => $query->whereDate('purchase_date', '>=', $value),
            'to_date' => fn($query, $value) => $query->whereDate('purchase_date', '<=', $value),
        ];

        foreach ($filterableFields as $field => $callback) {
            if (!empty($filters[$field])) {
                $callback($query, $filters[$field]);
            }
        }

        return $query->with([
            'account:id,account_holder_name',
            'vendor:id,first_name,last_name',
            'items.product:id,name',
            'items.unit:id,name',
        ])
            ->latest('purchase_date')
            ->paginate($filters['per_page'] ?? 10);
    }


    public function store(array $data): BandwidthPurchase
    {
        $items = $data['items'] ?? [];

        return DB::transaction(function () use ($data, $items) {

            $bandwidthPurchase = BandwidthPurchase::create([
                'account_id'        => $data['account_id'],
                'vendor_id'         => $data['vendor_id'],
                'link'              => $data['link'] ?? null,
                'total_amount'      => $data['total_amount'] ?? 0,
                'discount_type'     => $data['discount_type'] ?? null,
                'discount_value'    => $data['discount_value'] ?? 0,
                'additional_amount' => $data['additional_amount'] ?? 0,
                'paid_amount'       => $data['paid_amount'] ?? 0,
                'due_amount'        => $data['due_amount'] ?? 0,
                'purchase_date'     => $data['purchase_date'],
                'invoice'           => $data['invoice'] ?? null,
                'created_by'        => authId(),
            ]);


            foreach ($items as $item) {
                $bandwidthPurchase->items()->create([
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                    'unit_id'      => $item['unit_id'],
                    'amount'       => $item['amount'],
                    'total_amount' => $item['quantity'] * $item['amount'],
                ]);
            }

            Transaction::create([
                'branch_id'        => $data['branch_id'] ?? null,
                'type'             => 'debit',
                'account_id'       => $bandwidthPurchase['account_id'],
                'description'      => "Bandwidth purchase payment to vendor ID {$bandwidthPurchase['vendor_id']}",
                'amount'           => $bandwidthPurchase['paid_amount'] ?? 0,
                'transaction_date' => now(),
                'created_by'       => authId(),
            ]);


            log_activity(
                'Created a new bandwidth purchase',
                authId(),
                null,
                'bandwidth_purchase',
                'created',
                [
                    'id'            => $bandwidthPurchase->id,
                    'vendor_id'     => $bandwidthPurchase->vendor_id,
                    'account_id'    => $bandwidthPurchase->account_id,
                    'total_amount'  => $bandwidthPurchase->total_amount,
                    'purchase_date' => $bandwidthPurchase->purchase_date,
                ]
            );

            return $bandwidthPurchase;
        });
    }


    public function show(string $id, array $relations = [], bool $throwException = true): BandwidthPurchase
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }
}
