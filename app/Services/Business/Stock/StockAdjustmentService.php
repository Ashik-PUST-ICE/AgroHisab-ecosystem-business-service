<?php

namespace App\Services\Business\Stock;

use App\Models\Business\StockAdjustment;
use App\Models\Business\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StockAdjustmentService
{
    /**
     * Paginated list of stock adjustments/losses.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = StockAdjustment::with(['product:id,name', 'unit:id,name,symbol', 'market:id,name']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['loss_type'])) {
            $query->where('loss_type', $filters['loss_type']);
        }
        if (!empty($filters['market_id'])) {
            $query->where('market_id', $filters['market_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('adjustment_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('adjustment_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Record a stock loss or shortage adjustment.
     * Atomically creates StockAdjustment and StockMovement.
     */
    public function create(array $data, ?int $userId = null): StockAdjustment
    {
        return DB::transaction(function () use ($data, $userId) {
            $date = Carbon::parse($data['adjustment_date'] ?? now());
            $adjustmentNumber = $this->generateAdjustmentNumber($date);

            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'product_id'        => $data['product_id'],
                'market_id'         => $data['market_id'] ?? null,
                'loss_type'         => $data['loss_type'],
                'quantity'          => $data['quantity'],
                'unit_id'           => $data['unit_id'],
                'adjustment_date'   => $date->toDateString(),
                'reason'            => $data['reason'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $userId,
            ]);

            // Deduct from stock: damage/loss movement
            $type = defined('STOCK_DAMAGE') ? STOCK_DAMAGE : 6;
            $typeLabel = function_exists('stock_loss_type_label')
                ? stock_loss_type_label((int) $data['loss_type'])
                : 'Stock Adjustment';

            StockMovement::create([
                'product_id'     => $adjustment->product_id,
                'market_id'      => $adjustment->market_id,
                'type'           => $type,
                'quantity'       => -$adjustment->quantity, // negative to reduce stock
                'unit_id'        => $adjustment->unit_id,
                'reference_type' => StockAdjustment::class,
                'reference_id'   => $adjustment->id,
                'notes'          => "Loss: {$typeLabel} - " . ($adjustment->reason ?? 'Adjusted stock'),
                'created_by'     => $userId,
            ]);

            return $adjustment->load(['product', 'unit', 'market']);
        });
    }

    /**
     * Find single adjustment with relations.
     */
    public function find(int $id): StockAdjustment
    {
        $adjustment = StockAdjustment::with(['product', 'unit', 'market'])->find($id);
        if (!$adjustment) {
            throw new NotFoundHttpException('Stock adjustment record not found.');
        }
        return $adjustment;
    }

    /**
     * Delete/revert adjustment: removes stock movement and soft deletes.
     */
    public function delete(StockAdjustment $adjustment): bool
    {
        return DB::transaction(function () use ($adjustment) {
            // Remove linked stock movement
            StockMovement::where('reference_type', StockAdjustment::class)
                ->where('reference_id', $adjustment->id)
                ->delete();

            return $adjustment->delete();
        });
    }

    private function generateAdjustmentNumber(Carbon $date): string
    {
        $prefix = 'ADJ-' . $date->format('Ymd');
        $last = StockAdjustment::where('adjustment_number', 'like', "{$prefix}-%")
            ->latest('id')
            ->value('adjustment_number');

        if ($last) {
            $seq = (int) substr($last, strrpos($last, '-') + 1) + 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%03d', $prefix, $seq);
    }
}