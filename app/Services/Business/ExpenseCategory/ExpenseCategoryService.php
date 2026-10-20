<?php

namespace App\Services\Business\ExpenseCategory;

use App\Models\Business\ExpenseCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseCategoryService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return ExpenseCategory::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): ExpenseCategory
    {
        $data['created_by'] = authId();

        return ExpenseCategory::create($data);
    }

    public function show(ExpenseCategory $expenseCategory): ExpenseCategory
    {
        return $expenseCategory;
    }

    public function update(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        $expenseCategory->update($data);

        return $expenseCategory->fresh();
    }

    public function destroy(ExpenseCategory $expenseCategory): void
    {
        $expenseCategory->delete();
    }
}