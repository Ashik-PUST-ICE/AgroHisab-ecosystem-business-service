<?php

namespace App\Services\Modules\Finance;

use App\Enums\Commons\PaymentStatus\PaymentStatusEnum;
use App\Models\Account;
use App\Models\Expense;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    protected Expense $model;

    public function __construct()
    {
        $this->model = new Expense;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('bill_no', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Expense
    {
        $authId = $data['created_by'] ?? authId();

        return DB::transaction(function () use ($data, $authId) {
            $expense = Expense::create([
                'title' => $data['title'],
                'bill_no' => $data['bill_no'],
                'expense_date' => $data['expense_date'],
                'account_id' => $data['account_id'] ?? null,
                'amount' => $data['amount'],
                'voucher' => $data['voucher'] ?? null,
                'payment_status' => $data['payment_status'] ?? 0,
                'created_by' => $authId,
                'description' => $data['description'] ?? null,
                'finance_category_id' => $data['finance_category_id'] ?? null,
            ]);

            if (isset($data['account_id']) && $data['amount'] > 0) {
                $account = Account::lockForUpdate()->findOrFail($data['account_id']);

                $transaction = $expense->transactions()->create([
                    'branch_id' => $account->branch_id,
                    'type' => 'debit',
                    'account_id' => $account->id,
                    'description' => "Expense: {$expense->title}",
                    'amount' => $data['amount'],
                    'transaction_date' => $data['expense_date'],
                    'created_by' => $authId,
                ]);

                $account->decrement('opening_balance', $data['amount']);

                log_activity('Expense created with transaction', $authId, null, 'expense', 'created', ['id' => $expense->id, 'title' => $expense->title, 'bill_no' => $expense->bill_no, 'expense_date' => $expense->expense_date, 'amount' => $expense->amount]);
            }

            return $expense;
        });
    }

    public function show(string $id, $relations = [], $throwException = true): Expense
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Expense
    {
        $authId = $data['updated_by'] ?? authId();

        return DB::transaction(function () use ($id, $data, $authId) {

            $expense = Expense::with('transactions')->lockForUpdate()->findOrFail($id);

            if ($expense->account_id && $expense->amount != 0) {
                $oldAccount = Account::lockForUpdate()->findOrFail($expense->account_id);
                $oldAccount->increment('opening_balance', $expense->amount);
            }

            $expense->update([
                'title' => $data['title'],
                'bill_no' => $data['bill_no'],
                'expense_date' => $data['expense_date'],
                'account_id' => $data['account_id'] ?? null,
                'amount' => $data['amount'],
                'voucher' => $data['voucher'] ?? null,
                'payment_status' => $data['payment_status'] ?? 0,
                'description' => $data['description'] ?? null,
                'finance_category_id' => $data['finance_category_id'] ?? null,
            ]);

            $expense->transactions()->delete();

            if (isset($data['account_id']) && $data['amount'] != 0) {
                $newAccount = Account::lockForUpdate()->findOrFail($data['account_id']);

                $transaction = $expense->transactions()->create([
                    'branch_id' => $newAccount->branch_id,
                    'type' => 'debit',
                    'account_id' => $newAccount->id,
                    'description' => "Expense: {$expense->title}",
                    'amount' => $data['amount'],
                    'transaction_date' => $data['expense_date'],
                    'created_by' => $authId,
                ]);

                $newAccount->decrement('opening_balance', $data['amount']);

                log_activity('Expense updated with transaction', $authId, null, 'expense', 'updated', $expense->getChanges() + ['id' => $expense->id]);
            }

            return $expense->fresh();
        });
    }

    public function destroy(string $id): void
    {
        $expense = $this->show($id);
        $expense->delete();
        log_activity('Deleted a expense', authId(), null, 'expense', 'deleted', $expense->toArray());
    }

    public function toggleStatus(string $id): Expense
    {
        $Expense = $this->show($id);
        $Expense->payment_status = match ($Expense->payment_status) {
            PaymentStatusEnum::PAID => PaymentStatusEnum::DUE,
            PaymentStatusEnum::DUE => PaymentStatusEnum::UNPAID,
            default => PaymentStatusEnum::PAID,
        };
        $Expense->save();
        log_activity('Toggled Expense status to '.($Expense->payment_status->label()), authId(), null, 'expense', 'updated', $Expense->getChanges() + ['id' => $Expense->id]);

        return $Expense;
    }
}
