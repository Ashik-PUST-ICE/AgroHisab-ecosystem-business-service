<?php

namespace App\Services\Business\Expense;

use App\Models\Business\Expense;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpenseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Expense::with(['category', 'shipment', 'account']);

        if (!empty($filters['expense_category_id'])) {
            $query->where('expense_category_id', $filters['expense_category_id']);
        }

        if (!empty($filters['shipment_id'])) {
            $query->where('shipment_id', $filters['shipment_id']);
        }

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('expense_date', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('expense_date', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('expense_number', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        return $query->latest('expense_date')->paginate($perPage);
    }

    public function store(array $data): Expense
    {
        $data['expense_number'] = $this->generateExpenseNumber();
        $data['expense_date']   = Carbon::parse($data['expense_date'])->toDateString();
        $data['created_by']     = authId();

        $expense = Expense::create($data);

        return $expense->load(['category', 'shipment', 'account']);
    }

    public function show(Expense $expense): Expense
    {
        return $expense->load(['category', 'shipment', 'account']);
    }

    public function update(Expense $expense, array $data): Expense
    {
        if (!empty($data['expense_date'])) {
            $data['expense_date'] = Carbon::parse($data['expense_date'])->toDateString();
        }

        $expense->update($data);

        return $expense->fresh(['category', 'shipment', 'account']);
    }

    public function destroy(Expense $expense): void
    {
        $expense->delete();
    }

    protected function generateExpenseNumber(): string
    {
        $prefix = 'EXP-' . Carbon::now()->format('Ymd') . '-';
        $latest = Expense::where('expense_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest->expense_number);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}