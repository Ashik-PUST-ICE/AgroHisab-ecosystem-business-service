<?php

namespace App\Services\Business\Account;

use App\Models\Business\Account;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Account::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%");
            }))
            ->when(isset($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Account
    {
        $data['created_by'] = authId();

        return Account::create($data);
    }

    public function show(Account $account): Account
    {
        return $account;
    }

    public function update(Account $account, array $data): Account
    {
        $account->update($data);

        return $account->fresh();
    }

    public function destroy(Account $account): void
    {
        $account->delete();
    }
}