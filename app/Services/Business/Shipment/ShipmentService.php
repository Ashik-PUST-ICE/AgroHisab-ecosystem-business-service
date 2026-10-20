<?php

namespace App\Services\Business\Shipment;

use App\Models\Business\Aratdar;
use App\Models\Business\PurchaseItem;
use App\Models\Business\Settlement;
use App\Models\Business\Shipment;
use App\Models\Business\ShipmentItem;
use App\Models\Business\ShipmentSale;
use App\Models\Business\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Shipment::with(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['aratdar_id'])) {
            $query->where('aratdar_id', $filters['aratdar_id']);
        }

        if (!empty($filters['transporter_id'])) {
            $query->where('transporter_id', $filters['transporter_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('dispatch_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('dispatch_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        return $query->latest('id')->paginate($perPage);
    }

    public function create(array $data): Shipment
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'];
            unset($data['items']);

            $data['shipment_number'] = $this->generateShipmentNumber();
            $data['status']          = SHIPMENT_STATUS_DRAFT;
            $data['created_by']      = authId();

            if (!empty($data['dispatch_date'])) {
                $data['dispatch_date'] = Carbon::parse($data['dispatch_date'])->toDateString();
            }
            if (!empty($data['expected_arrival_date'])) {
                $data['expected_arrival_date'] = Carbon::parse($data['expected_arrival_date'])->toDateString();
            }

                        if (isset($data['expenses'])) {
                $data['expense_breakdown'] = $this->formatExpenses($data['expenses']);
                unset($data['expenses']);
            }
            $shipment = Shipment::create($data);

            foreach ($itemsData as $item) {
                $purchaseItem = PurchaseItem::with('purchase')->findOrFail($item['purchase_item_id']);

                $shipment->items()->create([
                    'purchase_item_id'  => $purchaseItem->id,
                    'product_id'        => $purchaseItem->product_id,
                    'unit_id'           => $purchaseItem->unit_id,
                    'bag_count'         => $item['bag_count'] ?? 0,
                    'dispatched_weight' => $item['dispatched_weight'],
                    'unit_rate'         => $purchaseItem->unit_rate,
                    'notes'             => $item['notes'] ?? null,
                ]);
            }

            return $shipment->load(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);
        });
    }

    public function show(Shipment $shipment): Shipment
    {
        return $shipment->load(['aratdar', 'transporter', 'market', 'items.product', 'items.unit', 'settlement']);
    }

    public function update(Shipment $shipment, array $data): Shipment
    {
        if ($shipment->status !== SHIPMENT_STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'shipment' => ['Only Draft shipments can be updated.'],
            ]);
        }

        return DB::transaction(function () use ($shipment, $data) {
            if (isset($data['items'])) {
                $itemsData = $data['items'];
                unset($data['items']);

                $shipment->items()->delete();

                foreach ($itemsData as $item) {
                    $purchaseItem = PurchaseItem::findOrFail($item['purchase_item_id']);
                    $shipment->items()->create([
                        'purchase_item_id'  => $purchaseItem->id,
                        'product_id'        => $purchaseItem->product_id,
                        'unit_id'           => $purchaseItem->unit_id,
                        'bag_count'         => $item['bag_count'] ?? 0,
                        'dispatched_weight' => $item['dispatched_weight'],
                        'unit_rate'         => $purchaseItem->unit_rate,
                        'notes'             => $item['notes'] ?? null,
                    ]);
                }
            }

            if (!empty($data['dispatch_date'])) {
                $data['dispatch_date'] = Carbon::parse($data['dispatch_date'])->toDateString();
            }
            if (!empty($data['expected_arrival_date'])) {
                $data['expected_arrival_date'] = Carbon::parse($data['expected_arrival_date'])->toDateString();
            }

                        if (isset($data['expenses'])) {
                $data['expense_breakdown'] = $this->formatExpenses($data['expenses']);
                unset($data['expenses']);
            }
            $shipment->update($data);

            return $shipment->fresh(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);
        });
    }

    public function destroy(Shipment $shipment): void
    {
        if ($shipment->status !== SHIPMENT_STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'shipment' => ['Only Draft shipments can be deleted.'],
            ]);
        }

        $shipment->delete();
    }

    public function dispatch(Shipment $shipment, array $data): Shipment
    {
        if ($shipment->status !== SHIPMENT_STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'shipment' => ['Only Draft shipments can be dispatched.'],
            ]);
        }

        return DB::transaction(function () use ($shipment, $data) {
            $shipment->load('items');

            foreach ($shipment->items as $item) {
                StockMovement::create([
                    'product_id'        => $item->product_id,
                    'market_id'         => null,
                    'type'              => STOCK_DISPATCH,
                    'quantity'          => -$item->dispatched_weight,
                    'unit_id'           => $item->unit_id,
                    'reference_type'    => Shipment::class,
                    'reference_id'      => $shipment->id,
                    'reference_item_id' => $item->id,
                    'notes'             => "Stock dispatched via Shipment #{$shipment->shipment_number}",
                    'created_by'        => authId(),
                ]);
            }

            $dispatchDate = !empty($data['dispatch_date'])
                ? Carbon::parse($data['dispatch_date'])->toDateString()
                : Carbon::now()->toDateString();

            $shipment->update([
                'status'         => SHIPMENT_STATUS_DISPATCHED,
                'dispatch_date'  => $dispatchDate,
                'vehicle_number' => $data['vehicle_number'] ?? $shipment->vehicle_number,
            ]);

            return $shipment->fresh(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);
        });
    }

    public function receiveAtArat(Shipment $shipment, array $data): Shipment
    {
        if ($shipment->status !== SHIPMENT_STATUS_DISPATCHED) {
            throw ValidationException::withMessages([
                'shipment' => ['Only Dispatched shipments can be received at arat.'],
            ]);
        }

        return DB::transaction(function () use ($shipment, $data) {
            foreach ($data['items'] as $receiptItem) {
                $shipmentItem = ShipmentItem::findOrFail($receiptItem['shipment_item_id']);

                if ($shipmentItem->shipment_id !== $shipment->id) {
                    throw ValidationException::withMessages([
                        'items' => ["Shipment item #{$receiptItem['shipment_item_id']} does not belong to this shipment."],
                    ]);
                }

                $receivedWeight = (float) $receiptItem['received_weight'];
                $shortage = max(0, round((float) $shipmentItem->dispatched_weight - $receivedWeight, 4));

                $shipmentItem->update([
                    'received_weight' => $receivedWeight,
                    'shortage_weight' => $shortage,
                ]);

                StockMovement::create([
                    'product_id'        => $shipmentItem->product_id,
                    'market_id'         => $shipment->market_id,
                    'type'              => STOCK_RECEIVE,
                    'quantity'          => $receivedWeight,
                    'unit_id'           => $shipmentItem->unit_id,
                    'reference_type'    => Shipment::class,
                    'reference_id'      => $shipment->id,
                    'reference_item_id' => $shipmentItem->id,
                    'notes'             => "Goods received at arat for Shipment #{$shipment->shipment_number}",
                    'created_by'        => authId(),
                ]);

                if ($shortage > 0) {
                    StockMovement::create([
                        'product_id'        => $shipmentItem->product_id,
                        'market_id'         => $shipment->market_id,
                        'type'              => STOCK_DAMAGE,
                        'quantity'          => -$shortage,
                        'unit_id'           => $shipmentItem->unit_id,
                        'reference_type'    => Shipment::class,
                        'reference_id'      => $shipment->id,
                        'reference_item_id' => $shipmentItem->id,
                        'notes'             => "Shortage/damage on receipt for Shipment #{$shipment->shipment_number}. Reason: " . ($receiptItem['shortage_reason'] ?? 'unspecified'),
                        'created_by'        => authId(),
                    ]);
                }
            }

            $shipment->update([
                'status' => SHIPMENT_STATUS_RECEIVED,
            ]);

            return $shipment->fresh(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);
        });
    }

    public function getChallan(Shipment $shipment): array
    {
        $shipment->load(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);

        $totalBags = $shipment->items->sum('bag_count');
        $totalDispatchedWeight = (float) $shipment->items->sum('dispatched_weight');
        $totalReceivedWeight = (float) $shipment->items->sum('received_weight');

        $dispatchDate = $shipment->dispatch_date
            ? Carbon::parse($shipment->dispatch_date)->toDateString()
            : Carbon::now()->toDateString();

        $expectedArrival = $shipment->expected_arrival_date
            ? Carbon::parse($shipment->expected_arrival_date)->toDateString()
            : null;

        return [
            'challan_number'   => $shipment->shipment_number,
            'shipment_id'      => $shipment->id,
            'dispatch_date'    => $dispatchDate,
            'expected_arrival' => $expectedArrival,
            'status'           => $shipment->status,
            'status_label'     => shipment_status_label($shipment->status),
            'transporter'      => [
                'id'             => $shipment->transporter?->id,
                'name'           => $shipment->transporter?->name,
                'phone'          => $shipment->transporter?->phone,
                'vehicle_number' => $shipment->vehicle_number ?: $shipment->transporter?->vehicle_number,
                'driver_name'    => $shipment->transporter?->driver_name,
            ],
            'aratdar'          => [
                'id'            => $shipment->aratdar?->id,
                'name'          => $shipment->aratdar?->name,
                'business_name' => $shipment->aratdar?->business_name,
                'phone'         => $shipment->aratdar?->phone,
                'address'       => $shipment->aratdar?->address,
                'market'        => $shipment->aratdar?->market,
                'district'      => $shipment->aratdar?->district,
            ],
            'destination_market' => $shipment->market?->name,
            'freight'          => [
                'amount'  => (float) $shipment->freight_amount,
                'paid_by' => $shipment->freight_paid_by,
            ],
            'items'            => $shipment->items->map(function ($item) {
                return [
                    'id'                => $item->id,
                    'product_id'        => $item->product_id,
                    'product_name'      => $item->product?->name,
                    'unit_name'         => $item->unit?->name,
                    'bag_count'         => $item->bag_count,
                    'dispatched_weight' => (float) $item->dispatched_weight,
                    'received_weight'   => (float) $item->received_weight,
                    'shortage_weight'   => (float) $item->shortage_weight,
                    'notes'             => $item->notes,
                ];
            }),
            'totals'           => [
                'total_bags'              => $totalBags,
                'total_dispatched_weight' => $totalDispatchedWeight,
                'total_received_weight'   => $totalReceivedWeight,
            ],
            'notes'            => $shipment->notes,
        ];
    }

    public function renderChallanHtml(Shipment $shipment): string
    {
        $challan = $this->getChallan($shipment);

        $itemsRows = '';
        $sl = 1;
        foreach ($challan['items'] as $item) {
            $itemsRows .= "<tr>
                <td style='border:1px solid #ddd;padding:8px;text-align:center;'>{$sl}</td>
                <td style='border:1px solid #ddd;padding:8px;'><strong>{$item['product_name']}</strong></td>
                <td style='border:1px solid #ddd;padding:8px;text-align:center;'>{$item['bag_count']}</td>
                <td style='border:1px solid #ddd;padding:8px;text-align:right;'>{$item['dispatched_weight']} {$item['unit_name']}</td>
                <td style='border:1px solid #ddd;padding:8px;'>{$item['notes']}</td>
            </tr>";
            $sl++;
        }

        return "<!DOCTYPE html>
<html lang='bn'>
<head>
    <meta charset='UTF-8'>
    <title>ডেলিভারি চালান - {$challan['challan_number']}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #2e7d32; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2e7d32; font-size: 26px; }
        .header p { margin: 5px 0 0; color: #555; }
        .challan-badge { display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 4px; font-weight: bold; margin-top: 8px; }
        .grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .box { width: 48%; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; background: #fafafa; }
        .box h3 { margin: 0 0 8px; font-size: 14px; text-transform: uppercase; color: #1b5e20; border-bottom: 1px solid #c8e6c9; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #2e7d32; color: #fff; padding: 10px; border: 1px solid #2e7d32; text-align: left; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; padding-top: 20px; }
        .sig-block { text-align: center; width: 28%; border-top: 1px dashed #777; padding-top: 8px; font-size: 13px; }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class='no-print' style='text-align: right; margin-bottom: 15px;'>
        <button onclick='window.print()' style='background: #2e7d32; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;'>চালান প্রিন্ট / PDF</button>
    </div>

    <div class='header'>
        <h1>কৃষি হিসাব (AgroHisab) - ডেলিভারি চালান</h1>
        <div class='challan-badge'>চালান নং: {$challan['challan_number']}</div>
        <p>তারিখ: {$challan['dispatch_date']} | অবস্থা: {$challan['status_label']}</p>
    </div>

    <div class='grid'>
        <div class='box'>
            <h3>প্রাপক (আড়তদার বিবরণ)</h3>
            <p><strong>{$challan['aratdar']['business_name']}</strong> ({$challan['aratdar']['name']})</p>
            <p>ঠিকানা: {$challan['aratdar']['address']}, {$challan['aratdar']['market']}, {$challan['aratdar']['district']}</p>
            <p>ফোন: {$challan['aratdar']['phone']}</p>
        </div>
        <div class='box'>
            <h3>পরিবহন বিবরণ (ট্রান্সপোর্টার)</h3>
            <p><strong>{$challan['transporter']['name']}</strong></p>
            <p>গাড়ির নম্বর: <strong>{$challan['transporter']['vehicle_number']}</strong></p>
            <p>চালক: {$challan['transporter']['driver_name']} (ফোন: {$challan['transporter']['phone']})</p>
            <p>ভাড়া: ৳" . number_format($challan['freight']['amount'], 2) . " ({$challan['freight']['paid_by']})</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style='width: 50px; text-align: center;'>ক্র নং</th>
                <th>পণ্যের বিবরণ</th>
                <th style='text-align: center;'>বস্তার সংখ্যা</th>
                <th style='text-align: right;'>প্রেরিত ওজন</th>
                <th>মন্তব্য</th>
            </tr>
        </thead>
        <tbody>
            {$itemsRows}
        </tbody>
        <tfoot>
            <tr style='background: #f1f8e9; font-weight: bold;'>
                <td colspan='2' style='border:1px solid #ddd;padding:10px;text-align:right;'>মোট:</td>
                <td style='border:1px solid #ddd;padding:10px;text-align:center;'>{$challan['totals']['total_bags']} বস্তা</td>
                <td style='border:1px solid #ddd;padding:10px;text-align:right;'>{$challan['totals']['total_dispatched_weight']}</td>
                <td style='border:1px solid #ddd;padding:10px;'></td>
            </tr>
        </tfoot>
    </table>

    <div style='margin-top: 20px; font-size: 13px;'>
        <p><strong>বিশেষ দ্রষ্টব্য:</strong> {$challan['notes']}</p>
    </div>

    <div class='signatures'>
        <div class='sig-block'>প্রেরকের স্বাক্ষর</div>
        <div class='sig-block'>চালকের স্বাক্ষর</div>
        <div class='sig-block'>আড়তদার গ্রহণকারী স্বাক্ষর</div>
    </div>
</body>
</html>";
    }

    public function recordSale(Shipment $shipment, array $data): ShipmentSale
    {
        if (!in_array($shipment->status, [SHIPMENT_STATUS_RECEIVED, SHIPMENT_STATUS_SELLING])) {
            throw ValidationException::withMessages([
                'shipment' => ['Sales can only be recorded for Received or Selling shipments.'],
            ]);
        }

        return DB::transaction(function () use ($shipment, $data) {
            $quantity = (float) $data['quantity'];
            $unitRate = (float) $data['unit_rate'];
            $grossAmount = round($quantity * $unitRate, 2);
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $netAmount = max(0, round($grossAmount - $discountAmount, 2));

            $aratdar = $shipment->aratdar ?: Aratdar::find($shipment->aratdar_id);

            $commissionType = isset($data['commission_type'])
                ? (int) $data['commission_type']
                : ($aratdar?->commission_type ?? COMMISSION_TYPE_PERCENTAGE);

            $commissionRate = isset($data['commission_rate'])
                ? (float) $data['commission_rate']
                : (float) ($aratdar?->commission_value ?? 0);

            $commissionBase = $aratdar?->commission_base ?? 'gross_sales';

            if ($commissionType === COMMISSION_TYPE_PERCENTAGE) {
                $base = ($commissionBase === 'net_sales') ? $netAmount : $grossAmount;
                $commissionAmount = round($base * ($commissionRate / 100), 2);
            } else {
                $commissionAmount = round($quantity * $commissionRate, 2);
            }

            $netProceeds = max(0, round($netAmount - $commissionAmount, 2));

            $saleNumber = $this->generateSaleNumber();
            $saleDate = Carbon::parse($data['sale_date'])->toDateString();

            $sale = ShipmentSale::create([
                'sale_number'       => $saleNumber,
                'shipment_id'       => $shipment->id,
                'shipment_item_id'  => $data['shipment_item_id'] ?? null,
                'product_id'        => $data['product_id'],
                'unit_id'           => $data['unit_id'] ?? null,
                'sale_date'         => $saleDate,
                'quantity'          => $quantity,
                'unit_rate'         => $unitRate,
                'gross_amount'      => $grossAmount,
                'discount_amount'   => $discountAmount,
                'net_amount'        => $netAmount,
                'commission_type'   => $commissionType,
                'commission_rate'   => $commissionRate,
                'commission_amount' => $commissionAmount,
                'net_proceeds'      => $netProceeds,
                'buyer_name'        => $data['buyer_name'] ?? null,
                'buyer_phone'       => $data['buyer_phone'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'created_by'        => authId(),
            ]);

            StockMovement::create([
                'product_id'        => $sale->product_id,
                'market_id'         => $shipment->market_id,
                'type'              => STOCK_SALE,
                'quantity'          => -$quantity,
                'unit_id'           => $sale->unit_id,
                'reference_type'    => ShipmentSale::class,
                'reference_id'      => $sale->id,
                'reference_item_id' => $sale->shipment_item_id,
                'notes'             => "Sale recorded for Shipment #{$shipment->shipment_number}. Buyer: " . ($sale->buyer_name ?: 'Walk-in'),
                'created_by'        => authId(),
            ]);

            if ($shipment->status === SHIPMENT_STATUS_RECEIVED) {
                $shipment->update(['status' => SHIPMENT_STATUS_SELLING]);
            }

            return $sale->load(['product', 'unit', 'shipmentItem']);
        });
    }

    public function getSales(Shipment $shipment): Collection
    {
        return $shipment->sales()->with(['product', 'unit', 'shipmentItem'])->latest('sale_date')->get();
    }

    public function getStockBalance(Shipment $shipment): array
    {
        $shipment->load(['items.product', 'items.unit', 'sales']);

        $itemsSummary = [];
        $totalDispatched = 0;
        $totalReceived = 0;
        $totalShortage = 0;
        $totalSold = 0;
        $totalRemaining = 0;
        $totalGrossSales = 0;
        $totalCommission = 0;
        $totalNetProceeds = 0;

        foreach ($shipment->items as $item) {
            $dispatched = (float) $item->dispatched_weight;
            $received   = (float) $item->received_weight;
            $shortage   = (float) $item->shortage_weight;

            $itemSales = $shipment->sales->filter(function ($s) use ($item) {
                return $s->shipment_item_id === $item->id || (!$s->shipment_item_id && $s->product_id === $item->product_id);
            });

            $sold = (float) $itemSales->sum('quantity');
            $remaining = max(0, round($received - $sold, 4));
            $gross = (float) $itemSales->sum('gross_amount');
            $comm = (float) $itemSales->sum('commission_amount');
            $proceeds = (float) $itemSales->sum('net_proceeds');

            $totalDispatched += $dispatched;
            $totalReceived += $received;
            $totalShortage += $shortage;
            $totalSold += $sold;
            $totalRemaining += $remaining;
            $totalGrossSales += $gross;
            $totalCommission += $comm;
            $totalNetProceeds += $proceeds;

            $itemsSummary[] = [
                'shipment_item_id'       => $item->id,
                'product_id'             => $item->product_id,
                'product_name'           => $item->product?->name,
                'unit_id'                => $item->unit_id,
                'unit_name'              => $item->unit?->name,
                'bag_count'              => $item->bag_count,
                'dispatched_weight'      => $dispatched,
                'received_weight'        => $received,
                'shortage_weight'        => $shortage,
                'sold_weight'            => $sold,
                'remaining_unsold_weight'=> $remaining,
                'gross_sales_amount'     => $gross,
                'commission_amount'      => $comm,
                'net_proceeds'           => $proceeds,
                'status'                 => $remaining <= 0 && $received > 0 ? 'fully_sold' : ($sold > 0 ? 'partially_sold' : 'unsold'),
            ];
        }

        return [
            'shipment_id'             => $shipment->id,
            'shipment_number'         => $shipment->shipment_number,
            'status'                  => $shipment->status,
            'status_label'            => shipment_status_label($shipment->status),
            'items'                   => $itemsSummary,
            'totals'                  => [
                'total_dispatched_weight' => round($totalDispatched, 4),
                'total_received_weight'   => round($totalReceived, 4),
                'total_shortage_weight'   => round($totalShortage, 4),
                'total_sold_weight'       => round($totalSold, 4),
                'total_remaining_unsold'  => round($totalRemaining, 4),
                'total_gross_sales'       => round($totalGrossSales, 2),
                'total_commission'        => round($totalCommission, 2),
                'total_net_proceeds'      => round($totalNetProceeds, 2),
            ],
        ];
    }

    public function getSettlementPreview(Shipment $shipment): array
    {
        $shipment->load(['aratdar', 'transporter', 'items.product', 'sales', 'allocations.remittance.account', 'expenses.category']);

        $stockBalance = $this->getStockBalance($shipment);

        $grossSales = (float) $stockBalance['totals']['total_gross_sales'];
        $commission = (float) $stockBalance['totals']['total_commission'];
        $netSalesProceeds = (float) $stockBalance['totals']['total_net_proceeds'];

        $freightAmount = (float) $shipment->freight_amount;
        $freightPaidBy = $shipment->freight_paid_by;

        // Linked expenses from Expense relation + dynamic JSON expense_breakdown (বস্তা, কুলি, ভাড়া, টোল ইত্যাদি)
        $linkedOwnerExpenses = (float) $shipment->expenses->where('paid_by', 'owner')->sum('amount');
        $linkedAratdarExpenses = (float) $shipment->expenses->where('paid_by', 'aratdar')->sum('amount');

        if (!empty($shipment->expense_breakdown) && is_array($shipment->expense_breakdown)) {
            foreach ($shipment->expense_breakdown as $item) {
                $amt = (float) ($item['amount'] ?? 0);
                $payer = $item['paid_by'] ?? 'owner';
                if ($payer === 'owner') {
                    $linkedOwnerExpenses += $amt;
                } else {
                    $linkedAratdarExpenses += $amt;
                }
            }
        }

        $totalRemitted = (float) $shipment->allocations->sum('amount');

        $aratdarFreightDeduction = ($freightPaidBy === 'aratdar') ? $freightAmount : 0;
        $totalAratdarDeductions = round($aratdarFreightDeduction + $linkedAratdarExpenses, 2);
        $expectedNetProceeds = max(0, round($netSalesProceeds - $totalAratdarDeductions, 2));

        $outstandingReceivable = max(0, round($expectedNetProceeds - $totalRemitted, 2));
        $excessRemitted        = max(0, round($totalRemitted - $expectedNetProceeds, 2));

        $allocatedCogs = 0;
        $unsoldInventoryValue = 0;

        foreach ($shipment->items as $item) {
            $unitRate = (float) $item->unit_rate;
            $itemSales = $shipment->sales->filter(fn($s) => $s->shipment_item_id === $item->id || (!$s->shipment_item_id && $s->product_id === $item->product_id));
            $soldWeight = (float) $itemSales->sum('quantity');
            $receivedWeight = (float) $item->received_weight;
            $unsoldWeight = max(0, $receivedWeight - $soldWeight);

            $allocatedCogs += round($soldWeight * $unitRate, 2);
            $unsoldInventoryValue += round($unsoldWeight * $unitRate, 2);
        }

        $ownerExpenses = ($freightPaidBy === 'owner') ? $freightAmount : 0;
        $totalOwnerExpenses = round($ownerExpenses + $linkedOwnerExpenses, 2);
        $realizedProfit = round($netSalesProceeds - $allocatedCogs - $totalOwnerExpenses - $linkedAratdarExpenses, 2);

        $allStockSold = ($stockBalance['totals']['total_remaining_unsold'] <= 0) && ($stockBalance['totals']['total_received_weight'] > 0);

        return [
            'shipment' => [
                'id'              => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'status'          => $shipment->status,
                'status_label'    => shipment_status_label($shipment->status),
                'aratdar'         => [
                    'id'            => $shipment->aratdar?->id,
                    'name'          => $shipment->aratdar?->name,
                    'business_name' => $shipment->aratdar?->business_name,
                ],
            ],
            'sales_summary' => [
                'gross_sales'        => $grossSales,
                'commission_amount'  => $commission,
                'net_sales_proceeds' => $netSalesProceeds,
            ],
            'freight_summary' => [
                'freight_amount'            => $freightAmount,
                'freight_paid_by'           => $freightPaidBy,
                'aratdar_freight_deduction' => $aratdarFreightDeduction,
                'owner_freight_expense'     => $ownerExpenses,
            ],
            'expenses_breakdown' => [
                'linked_owner_expenses'     => $linkedOwnerExpenses,
                'linked_aratdar_expenses'   => $linkedAratdarExpenses,
                'total_owner_expenses'      => $totalOwnerExpenses,
                'total_aratdar_deductions'  => $totalAratdarDeductions,
                'items'                     => $shipment->expenses->map(fn($e) => [
                    'id'            => $e->id,
                    'title'         => $e->title,
                    'category'      => $e->category?->name,
                    'amount'        => (float) $e->amount,
                    'paid_by'       => $e->paid_by,
                    'expense_date'  => $e->expense_date?->format('Y-m-d'),
                ]),
            ],
            'remittance_reconciliation' => [
                'expected_net_proceeds'  => $expectedNetProceeds,
                'total_remitted_amount'  => $totalRemitted,
                'outstanding_receivable' => $outstandingReceivable,
                'excess_remitted'        => $excessRemitted,
                'allocations'            => $shipment->allocations->map(fn($a) => [
                    'id'                => $a->id,
                    'remittance_number' => $a->remittance?->remittance_number,
                    'amount'            => (float) $a->amount,
                    'allocation_date'   => $a->allocation_date ? Carbon::parse($a->allocation_date)->toDateString() : null,
                    'account_name'      => $a->remittance?->account?->name,
                ]),
            ],
            'profit_and_loss' => [
                'allocated_cogs_sold_stock' => $allocatedCogs,
                'unsold_inventory_asset'    => $unsoldInventoryValue,
                'owner_consignment_expenses'=> $totalOwnerExpenses,
                'aratdar_consignment_deductions' => $totalAratdarDeductions,
                'realized_profit_or_loss'   => $realizedProfit,
            ],
            'settlement_status' => [
                'can_settle'     => $allStockSold && $outstandingReceivable <= 0,
                'all_stock_sold' => $allStockSold,
                'payment_cleared'=> $outstandingReceivable <= 0,
            ],
        ];
    }

    public function settle(Shipment $shipment, array $data = []): Settlement
    {
        if ($shipment->status === SHIPMENT_STATUS_SETTLED) {
            throw ValidationException::withMessages([
                'shipment' => ['This shipment has already been finalized and settled.'],
            ]);
        }

        $preview = $this->getSettlementPreview($shipment);

        return DB::transaction(function () use ($shipment, $preview, $data) {
            $settlementNumber = $this->generateSettlementNumber();
            $settlementDate   = !empty($data['settlement_date'])
                ? Carbon::parse($data['settlement_date'])->toDateString()
                : Carbon::now()->toDateString();

            $settlement = Settlement::create([
                'settlement_number'     => $settlementNumber,
                'shipment_id'           => $shipment->id,
                'aratdar_id'            => $shipment->aratdar_id,
                'settlement_date'       => $settlementDate,
                'gross_sales'           => $preview['sales_summary']['gross_sales'],
                'sale_discounts'        => 0,
                'net_sales'             => $preview['sales_summary']['net_sales_proceeds'] + $preview['sales_summary']['commission_amount'],
                'commission_amount'     => $preview['sales_summary']['commission_amount'],
                'owner_freight'         => $preview['freight_summary']['owner_freight_expense'],
                'aratdar_freight'       => $preview['freight_summary']['aratdar_freight_deduction'],
                'other_deductions'      => (float) ($data['other_deductions'] ?? 0),
                'expected_net_proceeds' => $preview['remittance_reconciliation']['expected_net_proceeds'],
                'remitted_amount'       => $preview['remittance_reconciliation']['total_remitted_amount'],
                'outstanding_balance'   => $preview['remittance_reconciliation']['outstanding_receivable'],
                'allocated_cogs'        => $preview['profit_and_loss']['allocated_cogs_sold_stock'],
                'realized_profit'       => $preview['profit_and_loss']['realized_profit_or_loss'] - (float) ($data['other_deductions'] ?? 0),
                'status'                => SETTLEMENT_STATUS_APPROVED,
                'notes'                 => $data['notes'] ?? null,
                'approved_by'           => authId(),
                'approved_at'           => Carbon::now(),
            ]);

            $shipment->update([
                'status' => SHIPMENT_STATUS_SETTLED,
            ]);

            return $settlement->load(['shipment', 'aratdar']);
        });
    }

    public function getSettlement(Shipment $shipment): ?Settlement
    {
        return $shipment->settlement?->load(['shipment', 'aratdar']);
    }


    /**
     * Standard expense heads used in agro produce mal challan business.
     */
    /**
     * Standard expense heads used in agro produce mal challan business.
     */
    public function getStandardExpenseHeads(): array
    {
        return function_exists('shipment_expense_types') ? shipment_expense_types() : [];
    }

    /**
     * Normalize and format dynamic expenses with helper labels & default payers.
     */
    protected function formatExpenses(array $rawExpenses): array
    {
        $formatted = [];
        foreach ($rawExpenses as $exp) {
            $type = isset($exp['type']) ? (int) $exp['type'] : null;
            $head = $exp['head'] ?? ($type ? shipment_expense_type_label($type) : 'অন্যান্য খরচ');
            $paidBy = !empty($exp['paid_by'])
                ? $exp['paid_by']
                : ($type ? shipment_expense_type_default_payer($type) : 'owner');

            $formatted[] = [
                'type'    => $type,
                'head'    => $head,
                'amount'  => (float) ($exp['amount'] ?? 0),
                'paid_by' => $paidBy,
                'notes'   => $exp['notes'] ?? null,
            ];
        }
        return $formatted;
    }

    protected function generateShipmentNumber(): string
    {
        $prefix = 'SHP-' . Carbon::now()->format('Ymd') . '-';
        $latest = Shipment::where('shipment_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest->shipment_number);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    protected function generateSaleNumber(): string
    {
        $prefix = 'SLS-' . Carbon::now()->format('Ymd') . '-';
        $latest = ShipmentSale::where('sale_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest->sale_number);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    protected function generateSettlementNumber(): string
    {
        $prefix = 'SET-' . Carbon::now()->format('Ymd') . '-';
        $latest = Settlement::where('settlement_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest->settlement_number);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}