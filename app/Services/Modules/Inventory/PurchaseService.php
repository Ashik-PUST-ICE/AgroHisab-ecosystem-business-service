<?php

namespace App\Services\Modules\Inventory;

use App\Enums\Commons\RequisitionStatus\RequisitionStatusEnum;
use App\Enums\Commons\StockStatus\StockStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Requisition;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\VendorPayment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    protected Purchase $model;

    public function __construct()
    {
        $this->model = new Purchase;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $filterableFields = [
            'search' => function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('invoice_no', 'like', "%{$value}%")
                        ->orWhere('product_code', 'like', "%{$value}%")
                        ->orWhere('note', 'like', "%{$value}%");
                });
            },
            'vendor_id' => fn ($query, $value) => $query->where('vendor_id', $value),
            'requisition_id' => fn ($query, $value) => $query->where('requisition_id', $value),
            'brand_id' => fn ($query, $value) => $query->where('brand_id', $value),
            'unit_id' => fn ($query, $value) => $query->where('unit_id', $value),
            'purchase_by' => fn ($query, $value) => $query->where('purchase_by', $value),
            'date_from' => fn ($query, $value) => $query->whereDate('purchase_date', '>=', $value),
            'date_to' => fn ($query, $value) => $query->whereDate('purchase_date', '<=', $value),
        ];

        foreach ($filterableFields as $field => $callback) {
            if (! empty($filters[$field])) {
                $callback($query, $filters[$field]);
            }
        }

        return $query->with([
            'product' => function ($q) {
                $q->with('stockCategory:id,name');
            },
            'vendor:id,first_name,last_name',
            'brand:id,name',
            'unit:id,name',
            'requisition:id,requisition_code',
        ])
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Purchase
    {
        $requisition = Requisition::with('product')->findOrFail($data['requisition_id']);
        if ($requisition->status == RequisitionStatusEnum::PURCHASED) {
            throw new \Exception('This requisition is already purchased.');
        }
        if ($requisition->status !== RequisitionStatusEnum::APPROVED) {
            throw new \Exception('This requisition must be approved before purchase.');
        }

        $data['purchase_by'] = authId();
        $items = $data['items'] ?? [];
        unset($data['items']);

        return DB::transaction(function () use ($data, $items, $requisition) {

            $purchase = $this->model->create([
                'requisition_id' => $requisition->id,
                'product_id' => $requisition->product_id,
                'vendor_id' => $requisition->vendor_id,
                'brand_id' => $data['brand_id'] ?? $requisition->brand_id,
                'unit_id' => $data['unit_id'] ?? $requisition->unit_id,
                'warranty' => $data['warranty'] ?? null,
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'total_price' => $data['total_price'] ?? null,
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'additional_charge' => $data['additional_charge'] ?? 0,
                'total_amount' => $data['total_amount'] ?? 0,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'due_amount' => $data['due_amount'] ?? 0,
                'purchase_date' => $data['purchase_date'] ?? now(),
                'invoice_no' => $data['invoice_no'] ?? null,
                'invoice_document' => $data['invoice_document'] ?? null,
                'product_code' => $data['product_code'] ?? null,
                'account_id' => $data['account_id'],
                'purchase_by' => authId(),
            ]);

            $purchase->ledgers()->create([
                'vendor_id' => $purchase->vendor_id,
                'account_id' => $purchase->account_id,
                'entry_date' => $purchase->purchase_date,
                'description' => 'Purchase created - Invoice: '.($purchase->invoice_no ?? 'N/A'),
                'debit' => $purchase->total_amount,
                'credit' => 0,
                'balance' => $purchase->total_amount,
                'created_by' => authId(),
            ]);

            if ($purchase->paid_amount > 0) {
                $transaction = Transaction::create([
                    'type' => 'debit',
                    'account_id' => $purchase->account_id,
                    'description' => 'Purchase payment - Invoice: '.($purchase->invoice_no ?? 'N/A'),
                    'amount' => $purchase->paid_amount,
                    'transaction_date' => $purchase->purchase_date ?? now(),
                    'created_by' => authId(),
                    'transactionable_type' => Purchase::class,
                    'transactionable_id' => $purchase->id,
                ]);

                $purchase->ledgers()->create([
                    'vendor_id' => $purchase->vendor_id,
                    'account_id' => $purchase->account_id,
                    'transaction_id' => $transaction->id,
                    'entry_date' => $purchase->purchase_date,
                    'description' => 'Payment for purchase - Invoice: '.($purchase->invoice_no ?? 'N/A'),
                    'debit' => 0,
                    'credit' => $purchase->paid_amount,
                    'balance' => $purchase->total_amount - $purchase->paid_amount,
                    'created_by' => authId(),
                ]);

                VendorPayment::create([
                    'vendor_id' => $purchase->vendor_id,
                    'account_id' => $purchase->account_id,
                    'transaction_id' => $transaction->id,
                    'payment_date' => $purchase->purchase_date,
                    'amount' => $purchase->paid_amount,
                    'note' => 'Payment for purchase - Invoice: '.($purchase->invoice_no ?? 'N/A'),
                    'created_by' => authId(),
                ]);

                log_activity('Created a new transaction for purchase invoice '.($purchase->invoice_no ?? 'N/A'), authId(), null, 'transaction', 'created', ['id' => $purchase->id, 'account_id' => $purchase->account_id, 'amount' => $purchase->paid_amount]);
            }

            if (! empty($items)) {
                foreach ($items as $item) {

                    $purchaseItem = $purchase->purchaseItems()->create([
                        'serial_no' => $item['serial_no'],
                        'mac_address' => $item['mac_address'] ?? null,
                        'brand_id' => $item['brand_id'] ?? null,
                        'warranty' => $purchase->warranty,
                        'purchased_date' => $purchase->purchase_date ?? now(),
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);
                    Stock::create([
                        'stock_category_id' => $requisition?->product?->stock_category_id,
                        'stock_product_id' => $requisition->product_id,
                        'product_code' => $data['product_code'] ?? null,
                        'serial_no' => $item['serial_no'],
                        'mac_address' => $item['mac_address'] ?? null,
                        's_mtr' => $data['s_mtr'] ?? null,
                        'e_mtr' => $data['e_mtr'] ?? null,
                        'brand_id' => $item['brand_id'] ?? null,
                        'warranty' => $purchase->warranty,
                        'stock_date' => $purchase->purchase_date ?? now(),
                        'quantity' => 1,
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);

                    StockHistory::create([
                        'product_code' => $data['product_code'] ?? null,
                        'type' => 'purchase',
                        'stockable_type' => PurchaseItem::class,
                        'stockable_id' => $purchaseItem->id,
                        'quantity' => 1,
                        'unit_id' => $data['unit_id'] ?? $requisition->unit_id,
                        'stock_product_id' => $requisition->product_id,
                        'stock_category_id' => $requisition?->product?->stock_category_id,
                        'requisition_id' => $requisition->id,
                        'vendor_id' => $requisition->vendor_id,
                        'serial_no' => $item['serial_no'] ?? null,
                        'mac_address' => $item['mac_address'] ?? null,
                        's_mtr' => $data['s_mtr'] ?? null,
                        'e_mtr' => $data['e_mtr'] ?? null,
                        'brand_id' => $item['brand_id'] ?? null,
                        'warranty' => $data['warranty'] ?? $purchase->warranty,
                        'price' => $data['unit_price'],
                        'date' => $purchase->purchase_date ?? now(),
                        'admin_id' => authId(),
                    ]);
                }
            } else {

                $existingStock = Stock::where('product_code', $data['product_code'])
                    ->where('stock_product_id', $requisition->product_id)
                    ->first();

                if ($existingStock) {
                    $existingStock->update(['stock_date' => now(), 'quantity' => $existingStock->quantity + $data['quantity']]);
                } else {

                    Stock::create([
                        'stock_category_id' => $requisition?->product?->stock_category_id,
                        'stock_product_id' => $requisition->product_id,
                        'product_code' => $data['product_code'] ?? $requisition->product_code ?? null,
                        's_mtr' => $data['s_mtr'] ?? null,
                        'e_mtr' => $data['e_mtr'] ?? null,
                        'brand_id' => $requisition->brand_id,
                        'warranty' => $data['warranty'] ?? null,
                        'stock_date' => $purchase->purchase_date ?? now(),
                        'quantity' => $data['quantity'],
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);
                }

                StockHistory::create([
                    'product_code' => $data['product_code'] ?? null,
                    'type' => 'purchase',
                    'stockable_type' => Purchase::class,
                    'stockable_id' => $purchase->id,
                    's_mtr' => $data['s_mtr'] ?? null,
                    'e_mtr' => $data['e_mtr'] ?? null,
                    'quantity' => $data['quantity'],
                    'unit_id' => $data['unit_id'] ?? $requisition->unit_id,
                    'stock_product_id' => $requisition->product_id,
                    'stock_category_id' => $requisition?->product?->stock_category_id,
                    'requisition_id' => $requisition->id,
                    'vendor_id' => $requisition->vendor_id,
                    'serial_no' => null,
                    'mac_address' => null,
                    'brand_id' => $requisition->brand_id,
                    'warranty' => $data['warranty'] ?? null,
                    'price' => $data['unit_price'],
                    'date' => $purchase->purchase_date ?? now(),
                    'admin_id' => authId(),
                ]);
            }

            $requisition->update(['status' => RequisitionStatusEnum::PURCHASED]);

            log_activity('Created a new purchase', authId(), null, 'purchase', 'created', ['id' => $purchase->id, 'invoice_no' => $purchase->invoice_no, 'quantity' => $purchase->quantity, 'total_amount' => $purchase->total_amount]);

            return $purchase;
        });
    }

    public function show(string $id, array $relations = [], bool $throwException = true): ?Purchase
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

         public function stockHistory(string $purchaseId)
    {
        $comments = Purchase::with(['stockHistories'])
            ->whereHas('task', function ($query) use ($purchaseId) {
                $query->where('purchase_id', $purchaseId);
            })
            ->latest()
            ->get();

        return $comments ;
    }
}
