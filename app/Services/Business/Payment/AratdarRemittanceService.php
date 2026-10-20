<?php

namespace App\Services\Business\Payment;

use App\Models\Business\AratdarRemittance;
use App\Models\Business\RemittanceAllocation;
use App\Models\Business\Shipment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AratdarRemittanceService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = AratdarRemittance::with(['aratdar', 'account', 'allocations.shipment']);

        if (!empty($filters['aratdar_id'])) {
            $query->where('aratdar_id', $filters['aratdar_id']);
        }

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('payment_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('payment_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('remittance_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        return $query->latest('payment_date')->paginate($perPage);
    }

    public function store(array $data): AratdarRemittance
    {
        return DB::transaction(function () use ($data) {
            $allocationsData = $data['allocations'] ?? [];
            unset($data['allocations']);

            $amount = (float) $data['amount'];
            $data['remittance_number']  = $this->generateRemittanceNumber();
            $data['payment_date']       = Carbon::parse($data['payment_date'])->toDateString();
            $data['allocated_amount']   = 0;
            $data['unallocated_amount'] = $amount;
            $data['created_by']         = authId();

            $remittance = AratdarRemittance::create($data);

            if (!empty($allocationsData)) {
                $this->processAllocations($remittance, $allocationsData);
            }

            return $remittance->fresh(['aratdar', 'account', 'allocations.shipment']);
        });
    }

    public function show(AratdarRemittance $remittance): AratdarRemittance
    {
        return $remittance->load(['aratdar', 'account', 'allocations.shipment']);
    }

    public function allocate(AratdarRemittance $remittance, array $allocationsData): AratdarRemittance
    {
        return DB::transaction(function () use ($remittance, $allocationsData) {
            $this->processAllocations($remittance, $allocationsData);

            return $remittance->fresh(['aratdar', 'account', 'allocations.shipment']);
        });
    }

    protected function processAllocations(AratdarRemittance $remittance, array $allocationsData): void
    {
        $totalToAllocate = array_sum(array_column($allocationsData, 'amount'));

        if ($totalToAllocate > (float) $remittance->unallocated_amount) {
            throw ValidationException::withMessages([
                'allocations' => [
                    "Allocation sum ({$totalToAllocate}) exceeds remaining unallocated amount ({$remittance->unallocated_amount}).",
                ],
            ]);
        }

        foreach ($allocationsData as $item) {
            $shipment = Shipment::findOrFail($item['shipment_id']);

            if ($shipment->aratdar_id !== $remittance->aratdar_id) {
                throw ValidationException::withMessages([
                    'allocations' => [
                        "Shipment #{$shipment->shipment_number} does not belong to the remitting Aratdar.",
                    ],
                ]);
            }

            RemittanceAllocation::create([
                'aratdar_remittance_id' => $remittance->id,
                'shipment_id'           => $shipment->id,
                'amount'                => (float) $item['amount'],
                'allocation_date'       => Carbon::parse($remittance->payment_date)->toDateString(),
                'notes'                 => $item['notes'] ?? null,
                'created_by'            => authId(),
            ]);
        }

        $newAllocated   = round((float) $remittance->allocated_amount + $totalToAllocate, 2);
        $newUnallocated = max(0, round((float) $remittance->amount - $newAllocated, 2));

        $remittance->update([
            'allocated_amount'   => $newAllocated,
            'unallocated_amount' => $newUnallocated,
        ]);
    }

    protected function generateRemittanceNumber(): string
    {
        $prefix = 'REM-' . Carbon::now()->format('Ymd') . '-';
        $latest = AratdarRemittance::where('remittance_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest->remittance_number);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}