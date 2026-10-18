<?php

namespace App\Services\Modules\Finance;

use App\Models\Account;
use App\Models\Conveyance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConveyanceService
{
    protected Conveyance $model;

    public function __construct()
    {
        $this->model = new Conveyance;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('purpose', 'like', '%'.$filters['search'].'%')
                    ->orWhere('voucher', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Conveyance
    {
        $authId = $data['created_by'] ?? authId();

        return DB::transaction(function () use ($data, $authId) {
            $conveyance = Conveyance::create([
                'purpose' => $data['purpose'],
                'user_id' => $data['user_id'] ?? null,
                'voucher' => $data['voucher'] ?? null,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $data['amount'],
                'biling_date' => $data['biling_date'],
                'description' => $data['description'] ?? null,
                'created_by' => $authId,
                'finance_category_id' => $data['finance_category_id'] ?? null,
            ]);

            if (isset($data['account_id']) && $data['amount'] > 0) {
                $account = Account::lockForUpdate()->findOrFail($data['account_id']);

                $transaction = $conveyance->transactions()->create([
                    'branch_id' => $account->branch_id,
                    'type' => 'debit',
                    'account_id' => $account->id,
                    'description' => "Conveyance: {$conveyance->purpose}",
                    'amount' => $data['amount'],
                    'transaction_date' => $data['biling_date'],
                    'created_by' => $authId,
                ]);

                $account->decrement('opening_balance', $data['amount']);

                log_activity('Conveyance created with transaction', $authId, null, 'conveyance', 'created', [
                    'id' => $conveyance->id,
                    'purpose' => $conveyance->purpose,
                    'amount' => $conveyance->amount,
                ]);
            }

            return $conveyance;
        });
    }

    public function show(string $id, $relations = [], $throwException = true): Conveyance
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Conveyance
    {
        $authId = $data['updated_by'] ?? authId();

        return DB::transaction(function () use ($id, $data, $authId) {

            $conveyance = Conveyance::with('transactions')->lockForUpdate()->findOrFail($id);

            if ($conveyance->account_id && $conveyance->amount != 0) {
                $oldAccount = Account::lockForUpdate()->findOrFail($conveyance->account_id);
                $oldAccount->increment('opening_balance', $conveyance->amount);
            }

            $conveyance->update([
                'purpose' => $data['purpose'],
                'user_id' => $data['user_id'] ?? null,
                'voucher' => $data['voucher'] ?? null,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $data['amount'],
                'biling_date' => $data['biling_date'],
                'description' => $data['description'] ?? null,
                'finance_category_id' => $data['finance_category_id'] ?? null,
            ]);

            $conveyance->transactions()->delete();

            if (isset($data['account_id']) && $data['amount'] != 0) {
                $newAccount = Account::lockForUpdate()->findOrFail($data['account_id']);

                $transaction = $conveyance->transactions()->create([
                    'branch_id' => $newAccount->branch_id,
                    'type' => 'debit',
                    'account_id' => $newAccount->id,
                    'description' => "Conveyance: {$conveyance->purpose}",
                    'amount' => $data['amount'],
                    'transaction_date' => $data['biling_date'],
                    'created_by' => $authId,
                ]);

                $newAccount->decrement('opening_balance', $data['amount']);

                log_activity('Conveyance updated with transaction', $authId, null, 'conveyance', 'updated', $conveyance->getChanges() + ['id' => $conveyance->id]);
            }

            return $conveyance->fresh();
        });
    }

    public function destroy(string $id): void
    {
        $conveyance = $this->show($id);
        $conveyance->delete();
        log_activity('Deleted a conveyance', authId(), null, 'conveyance', 'deleted', $conveyance->toArray());
    }
}
