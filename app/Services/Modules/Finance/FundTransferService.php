<?php

namespace App\Services\Modules\Finance;

use App\Models\Account;
use App\Models\FundTransfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FundTransferService
{
    protected FundTransfer $model;

    public function __construct()
    {
        $this->model = new FundTransfer;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('description', 'like', '%'.$filters['search'].'%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): FundTransfer
    {
        $authId = $data['created_by'] ?? authId();

        if ($data['account_from'] === $data['account_to']) {
            throw new \InvalidArgumentException('Source and destination accounts cannot be the same.');
        }

        if ($data['amount'] <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        return DB::transaction(function () use ($data, $authId) {
            $accountFrom = Account::lockForUpdate()->findOrFail($data['account_from']);
            $accountTo = Account::lockForUpdate()->findOrFail($data['account_to']);

            if ($accountFrom->opening_balance < $data['amount']) {
                throw new \Exception('Insufficient balance in source account.');
            }

            $fundTransfer = FundTransfer::create([
                'account_from' => $accountFrom->id,
                'account_to' => $accountTo->id,
                'transfer_date' => now(),
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'created_by' => $authId,
            ]);

            $transactions = [
                [

                    'branch_id' => $data['branch_id'] ?? null,
                    'type' => 'debit',
                    'account_id' => $accountFrom->id,
                    'description' => "Fund transfer to Account ID {$accountTo->id}",
                    'amount' => $data['amount'],
                    'transaction_date' => now(),
                    'created_by' => $authId,
                ],
                [

                    'branch_id' => $data['branch_id'] ?? null,
                    'type' => 'credit',
                    'account_id' => $accountTo->id,
                    'description' => "Fund received from Account ID {$accountFrom->id}",
                    'amount' => $data['amount'],
                    'transaction_date' => now(),
                    'created_by' => $authId,
                ],
            ];

            $createdTransactions = $fundTransfer->transactions()->createMany($transactions);

            $accountFrom->decrement('opening_balance', $data['amount']);
            $accountTo->increment('opening_balance', $data['amount']);

            log_activity('Fund transfer completed', $authId, null, 'fund_transfer', 'created', ['id' => $fundTransfer->id, 'account_from' => $fundTransfer->account_from, 'account_to' => $fundTransfer->account_to, 'amount' => $fundTransfer->amount]);

            return $fundTransfer;
        });
    }

    public function show(string $id, $relations = [], $throwException = true): FundTransfer
    {
        $defaultRelations = ['accountFrom', 'accountTo', 'transactions'];
        $allRelations = array_merge($defaultRelations, $relations);

        $query = $this->model->with($allRelations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $attributes): FundTransfer
    {
        $authId = $attributes['updated_by'] ?? authId();

        if ($attributes['account_from'] === $attributes['account_to']) {
            throw new \InvalidArgumentException('Source and destination accounts cannot be the same.');
        }

        if ($attributes['amount'] <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        return DB::transaction(function () use ($id, $attributes, $authId) {
            $fundTransfer = FundTransfer::with('transactions')->lockForUpdate()->findOrFail($id);

            $oldFrom = Account::lockForUpdate()->findOrFail($fundTransfer->account_from);
            $oldTo = Account::lockForUpdate()->findOrFail($fundTransfer->account_to);

            $oldFrom->increment('opening_balance', $fundTransfer->amount);
            $oldTo->decrement('opening_balance', $fundTransfer->amount);

            $newFrom = Account::lockForUpdate()->findOrFail($attributes['account_from']);
            $newTo = Account::lockForUpdate()->findOrFail($attributes['account_to']);

            $fundTransfer->update([
                'account_from' => $newFrom->id,
                'account_to' => $newTo->id,
                'transfer_date' => $attributes['transfer_date'] ?? now(),
                'description' => $attributes['description'] ?? null,
                'amount' => $attributes['amount'],
            ]);

            $fundTransfer->transactions()->delete();

            $debitTransaction = $fundTransfer->transactions()->create([
                'branch_id' => $attributes['branch_id'],
                'type' => 'debit',
                'account_id' => $newFrom->id,
                'description' => "Fund transfer to Account ID {$newTo->id}",
                'amount' => $attributes['amount'],
                'transaction_date' => now(),
                'created_by' => $authId,
            ]);

            $creditTransaction = $fundTransfer->transactions()->create([
                'branch_id' => $attributes['branch_id'],
                'type' => 'credit',
                'account_id' => $newTo->id,
                'description' => "Fund received from Account ID {$newFrom->id}",
                'amount' => $attributes['amount'],
                'transaction_date' => now(),
                'created_by' => $authId,
            ]);

            // Apply new balances
            $newFrom->decrement('opening_balance', $attributes['amount']);
            $newTo->increment('opening_balance', $attributes['amount']);

            log_activity('Fund transfer updated', $authId, null, 'fund_transfer', 'updated', $fundTransfer->getChanges() + ['id' => $fundTransfer->id]);

            // return $fundTransfer->fresh(['transactions', 'accountFrom', 'accountTo']);
            return $fundTransfer->fresh();
        });
    }

    public function destroy(string $id): void
    {
        $FundTransfer = $this->show($id);
        $FundTransfer->delete();
        log_activity('Deleted a FundTransfer', authId(), null, 'fund_transfer', 'deleted', $FundTransfer->toArray());
    }
}
