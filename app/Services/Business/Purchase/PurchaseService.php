<?php

namespace App\Services\Business\Purchase;

use App\Models\Business\Purchase;
use App\Models\Business\PurchaseItem;
use App\Models\Business\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Purchase::query()
            ->with(['supplier', 'market', 'items.product', 'items.unit'])
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            }))
            ->when($filters['supplier_id'] ?? null, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($filters['market_id'] ?? null, fn($q, $marketId) => $q->where('market_id', $marketId))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when($filters['from_date'] ?? null, fn($q, $from) => $q->whereDate('purchase_date', '>=', $from))
            ->when($filters['to_date'] ?? null, fn($q, $to) => $q->whereDate('purchase_date', '<=', $to))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $discount = (float) ($data['discount_amount'] ?? 0);
            $paid = (float) ($data['paid_amount'] ?? 0);

            // Calculate total from items
            $totalAmount = 0;
            $preparedItems = [];
            foreach ($itemsData as $item) {
                $netWeight = (float) $item['net_weight'];
                $unitRate = (float) $item['unit_rate'];
                $lineTotal = round($netWeight * $unitRate, 2);
                $totalAmount += $lineTotal;

                $preparedItems[] = array_merge($item, [
                    'line_total' => $lineTotal,
                ]);
            }

            $finalTotal = max(0, round($totalAmount - $discount, 2));
            $dueAmount = max(0, round($finalTotal - $paid, 2));

            $data['purchase_number'] = $this->generatePurchaseNumber();
            $data['total_amount'] = $finalTotal;
            $data['due_amount'] = $dueAmount;
            $data['status'] = $data['status'] ?? PURCHASE_STATUS_DRAFT;
            $data['created_by'] = authId();

            $purchase = Purchase::create($data);

            foreach ($preparedItems as $item) {
                $purchase->items()->create($item);
            }

            return $purchase->load(['supplier', 'market', 'items.product', 'items.unit']);
        });
    }

    public function show(Purchase $purchase): Purchase
    {
        return $purchase->load(['supplier', 'market', 'items.product', 'items.unit']);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        if ($purchase->status === PURCHASE_STATUS_RECEIVED) {
            throw ValidationException::withMessages([
                'purchase' => ['Cannot modify a purchase that has already been received into stock.'],
            ]);
        }

        return DB::transaction(function () use ($purchase, $data) {
            if (isset($data['items'])) {
                $itemsData = $data['items'];
                unset($data['items']);

                $discount = (float) ($data['discount_amount'] ?? $purchase->discount_amount);
                $paid = (float) ($data['paid_amount'] ?? $purchase->paid_amount);

                $totalAmount = 0;
                $preparedItems = [];
                foreach ($itemsData as $item) {
                    $netWeight = (float) $item['net_weight'];
                    $unitRate = (float) $item['unit_rate'];
                    $lineTotal = round($netWeight * $unitRate, 2);
                    $totalAmount += $lineTotal;

                    $preparedItems[] = array_merge($item, [
                        'line_total' => $lineTotal,
                    ]);
                }

                $finalTotal = max(0, round($totalAmount - $discount, 2));
                $data['total_amount'] = $finalTotal;
                $data['due_amount'] = max(0, round($finalTotal - $paid, 2));

                $purchase->items()->delete();
                foreach ($preparedItems as $item) {
                    $purchase->items()->create($item);
                }
            }

            $purchase->update($data);

            return $purchase->fresh(['supplier', 'market', 'items.product', 'items.unit']);
        });
    }

    public function destroy(Purchase $purchase): void
    {
        if ($purchase->status === PURCHASE_STATUS_RECEIVED) {
            throw ValidationException::withMessages([
                'purchase' => ['Cannot delete a purchase that has already been received into stock.'],
            ]);
        }

        $purchase->delete();
    }

    public function receive(Purchase $purchase, array $data = []): Purchase
    {
        if ($purchase->status === PURCHASE_STATUS_RECEIVED) {
            throw ValidationException::withMessages([
                'purchase' => ['This purchase has already been received into stock.'],
            ]);
        }

        return DB::transaction(function () use ($purchase) {
            $purchase->load('items');

            foreach ($purchase->items as $item) {
                StockMovement::create([
                    'product_id'        => $item->product_id,
                    'market_id'         => $purchase->market_id,
                    'type'              => STOCK_PURCHASE,
                    'quantity'          => $item->net_weight,
                    'unit_id'           => $item->unit_id,
                    'reference_type'    => Purchase::class,
                    'reference_id'      => $purchase->id,
                    'reference_item_id' => $item->id,
                    'notes'             => "Stock received from Purchase #{$purchase->purchase_number}",
                    'created_by'        => authId(),
                ]);
            }

            $purchase->update([
                'status' => PURCHASE_STATUS_RECEIVED,
            ]);

            return $purchase->fresh(['supplier', 'market', 'items.product', 'items.unit', 'stockMovements']);
        });
    }


    /**
     * Quick Procurement Calculator (কাঁচামাল ক্রয় ক্যালকুলেটর):
     * কাঁচা ওজন (Actual KG), বাদ/কাটতি (Deducted KG), মণের দর (Rate per Mon) দিয়ে
     * তাত্ক্ষণিক প্রতি কেজি দর, কৃষকের পাওনা টাকা হিসাব করে এবং ঐচ্ছিকভাবে ১ ক্লিকে পারচেজ সেভ করে।
     */
    public function calculateProcurement(array $data): array
    {
        $actualWeight = (float) $data['actual_weight'];
        $deductedWeight = (float) ($data['deducted_weight'] ?? 0);
        $payableWeight = max(0, round($actualWeight - $deductedWeight, 4));

        $monSize = (float) ($data['mon_size'] ?? 40.00);
        if ($monSize <= 0) $monSize = 40.00;

        $monRate = (float) $data['mon_rate'];
        $ratePerKg = round($monRate / $monSize, 4);

        $totalProduceValue = round($payableWeight * $ratePerKg, 2);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);
        $dueAmount = max(0, round($totalProduceValue - $paidAmount, 2));

        $maundCount = round($payableWeight / $monSize, 2);

        $summaryBn = "কাঁচা ওজন: {$actualWeight} কেজি | কাটতি/বাদ: {$deductedWeight} কেজি | প্রদেয় ওজন: {$payableWeight} কেজি ({$maundCount} মণ)। "
            . "দর: {$monRate} টাকা/মণ ({$ratePerKg} টাকা/কেজি)। মোট দাম: {$totalProduceValue} টাকা। "
            . "পরিশোধ: {$paidAmount} টাকা, বাকি: {$dueAmount} টাকা।";

        $response = [
            'actual_weight'       => $actualWeight,
            'deducted_weight'     => $deductedWeight,
            'payable_weight'      => $payableWeight,
            'mon_rate'            => $monRate,
            'mon_size'            => $monSize,
            'rate_per_kg'         => $ratePerKg,
            'total_produce_value' => $totalProduceValue,
            'paid_amount'         => $paidAmount,
            'due_amount'          => $dueAmount,
            'summary_text_bn'     => $summaryBn,
        ];

        // If requested to save as purchase on the fly
        if (!empty($data['save_purchase']) && !empty($data['supplier_id']) && !empty($data['product_id'])) {
            $purchase = $this->store([
                'supplier_id'   => $data['supplier_id'],
                'purchase_date' => now()->toDateString(),
                'paid_amount'   => $paidAmount,
                'items'         => [
                    [
                        'product_id'      => $data['product_id'],
                        'actual_weight'   => $actualWeight,
                        'deducted_weight' => $deductedWeight,
                        'gross_weight'    => $actualWeight,
                        'tare_weight'     => $deductedWeight,
                        'net_weight'      => $payableWeight,
                        'mon_rate'        => $monRate,
                        'mon_size'        => $monSize,
                        'unit_rate'       => $ratePerKg,
                        'line_total'      => $totalProduceValue,
                        'notes'           => "কাটতি: {$deductedWeight} কেজি বাদ",
                    ]
                ],
                'notes'         => "ক্যালকুলেটর দিয়ে তৈরি ক্রয়: {$actualWeight} কেজিতে {$deductedWeight} কেজি কাটতি",
            ]);

            $response['purchase'] = $purchase;
        }

        return $response;
    }

    protected function generatePurchaseNumber(): string
    {
        $prefix = 'PUR-' . date('Ymd') . '-';
        $latest = Purchase::where('purchase_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest->purchase_number);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}