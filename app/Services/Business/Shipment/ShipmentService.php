<?php

namespace App\Services\Business\Shipment;

use App\Models\Business\Aratdar;
use App\Models\Business\PurchaseItem;
use App\Models\Business\Shipment;
use App\Models\Business\ShipmentItem;
use App\Models\Business\ShipmentSale;
use App\Models\Business\StockMovement;
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
        return $shipment->load(['aratdar', 'transporter', 'market', 'items.product', 'items.unit']);
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

            $shipment->update([
                'status'         => SHIPMENT_STATUS_DISPATCHED,
                'dispatch_date'  => $data['dispatch_date'],
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

                // Credit stock at destination arat market
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

                // Record shortage/damage if any
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

        return [
            'challan_number'   => $shipment->shipment_number,
            'shipment_id'      => $shipment->id,
            'dispatch_date'    => $shipment->dispatch_date?->format('Y-m-d'),
            'expected_arrival' => $shipment->expected_arrival_date?->format('Y-m-d'),
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

            // Dynamic Commission Computation from active Aratdar agreement or explicit override
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
                // Fixed rate per quantity
                $commissionAmount = round($quantity * $commissionRate, 2);
            }

            $netProceeds = max(0, round($netAmount - $commissionAmount, 2));

            $saleNumber = $this->generateSaleNumber();

            $sale = ShipmentSale::create([
                'sale_number'       => $saleNumber,
                'shipment_id'       => $shipment->id,
                'shipment_item_id'  => $data['shipment_item_id'] ?? null,
                'product_id'        => $data['product_id'],
                'unit_id'           => $data['unit_id'] ?? null,
                'sale_date'         => $data['sale_date'],
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

            // Deduct stock at Arat location
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

            // Shift shipment to Selling status if not already
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

            // Find sales linked to this item, or matching product
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

    protected function generateShipmentNumber(): string
    {
        $prefix = 'SHP-' . date('Ymd') . '-';
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
        $prefix = 'SLS-' . date('Ymd') . '-';
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
}