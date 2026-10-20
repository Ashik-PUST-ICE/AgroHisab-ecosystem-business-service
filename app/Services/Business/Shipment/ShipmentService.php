<?php

namespace App\Services\Business\Shipment;

use App\Models\Business\Purchase;
use App\Models\Business\PurchaseItem;
use App\Models\Business\Shipment;
use App\Models\Business\ShipmentItem;
use App\Models\Business\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Shipment::query()
            ->with(['aratdar', 'transporter', 'market'])
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%");
            }))
            ->when($filters['aratdar_id'] ?? null, fn($q, $id) => $q->where('aratdar_id', $id))
            ->when($filters['transporter_id'] ?? null, fn($q, $id) => $q->where('transporter_id', $id))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when($filters['from_date'] ?? null, fn($q, $from) => $q->whereDate('dispatch_date', '>=', $from))
            ->when($filters['to_date'] ?? null, fn($q, $to) => $q->whereDate('dispatch_date', '<=', $to))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Shipment
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $data['items'] ?? [];
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
}