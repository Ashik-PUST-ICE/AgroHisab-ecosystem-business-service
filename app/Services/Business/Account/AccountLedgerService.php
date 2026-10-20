<?php

namespace App\Services\Business\Account;

use App\Models\Business\Account;
use App\Models\Business\AccountTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountLedgerService
{
    /**
     * Get full account ledger with running balance.
     * Spec §3.11: "Record opening balance, deposits, withdrawals, receipts, payments, transfers"
     */
    public function ledger(Account $account, array $filters = []): array
    {
        $fromDate = !empty($filters['from_date']) ? Carbon::parse($filters['from_date'])->toDateString() : null;
        $toDate   = !empty($filters['to_date'])   ? Carbon::parse($filters['to_date'])->toDateString()   : null;

        $query = AccountTransaction::where('account_id', $account->id);
        if ($fromDate) $query->whereDate('txn_date', '>=', $fromDate);
        if ($toDate)   $query->whereDate('txn_date', '<=', $toDate);

        $transactions = $query->orderBy('txn_date')->orderBy('id')->get();

        // Opening balance (sum of all transactions before from_date + model opening_balance)
        $openingBalance = (float) $account->opening_balance;
        if ($fromDate) {
            $openingBalance += (float) AccountTransaction::where('account_id', $account->id)
                ->whereDate('txn_date', '<', $fromDate)
                ->sum(DB::raw('amount * direction'));
        }

        $totalCredit = 0.0;
        $totalDebit  = 0.0;
        $running = $openingBalance;

        $entries = [];
        foreach ($transactions as $txn) {
            $amount = (float) $txn->amount;
            if ($txn->direction === 1) {
                $totalCredit += $amount;
                $running += $amount;
                $debit = 0.00;
                $credit = $amount;
            } else {
                $totalDebit += $amount;
                $running -= $amount;
                $debit = $amount;
                $credit = 0.00;
            }

            $entries[] = [
                'id'             => $txn->id,
                'txn_number'     => $txn->txn_number,
                'txn_date'       => $txn->txn_date?->toDateString(),
                'type'           => $txn->type,
                'type_label'     => account_txn_type_label($txn->type),
                'description'    => $txn->description,
                'reference'      => $txn->reference,
                'debit'          => $debit,
                'credit'         => $credit,
                'running_balance'=> round($running, 2),
            ];
        }

        return [
            'account'         => [
                'id'              => $account->id,
                'name'            => $account->name,
                'type'            => $account->type,
                'opening_balance' => (float) $account->opening_balance,
            ],
            'period'          => ['from' => $fromDate, 'to' => $toDate],
            'summary'         => [
                'opening_balance' => round($openingBalance, 2),
                'total_credit'    => round($totalCredit, 2),
                'total_debit'     => round($totalDebit, 2),
                'closing_balance' => round($running, 2),
            ],
            'entries'         => $entries,
        ];
    }

    /**
     * Internal transfer between accounts.
     * Spec §3.11: "Internal transfer must not be counted as income or expense."
     * Creates two AccountTransaction records (debit from, credit to) atomically.
     */
    public function transfer(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $txnDate  = Carbon::parse($data['txn_date'])->toDateString();
            $amount   = (float) $data['amount'];
            $ref      = $data['reference'] ?? null;
            $desc     = $data['description'] ?? 'Internal transfer';
            $prefix   = 'TXN-' . Carbon::now()->format('Ymd') . '-';

            $seqBase = AccountTransaction::where('txn_number', 'like', "{$prefix}%")
                ->count();

            $debitTxn = AccountTransaction::create([
                'txn_number'       => $prefix . str_pad((string)($seqBase + 1), 4, '0', STR_PAD_LEFT),
                'account_id'       => $data['from_account_id'],
                'contra_account_id'=> $data['to_account_id'],
                'direction'        => -1,  // debit (money leaves)
                'type'             => 5,   // transfer_out
                'amount'           => $amount,
                'txn_date'         => $txnDate,
                'reference'        => $ref,
                'description'      => $desc,
                'created_by'       => authId(),
            ]);

            $creditTxn = AccountTransaction::create([
                'txn_number'       => $prefix . str_pad((string)($seqBase + 2), 4, '0', STR_PAD_LEFT),
                'account_id'       => $data['to_account_id'],
                'contra_account_id'=> $data['from_account_id'],
                'direction'        => 1,   // credit (money arrives)
                'type'             => 4,   // transfer_in
                'amount'           => $amount,
                'txn_date'         => $txnDate,
                'reference'        => $ref,
                'description'      => $desc,
                'created_by'       => authId(),
            ]);

            return [
                'debit_txn_number'  => $debitTxn->txn_number,
                'credit_txn_number' => $creditTxn->txn_number,
                'from_account_id'   => $data['from_account_id'],
                'to_account_id'     => $data['to_account_id'],
                'amount'            => $amount,
                'txn_date'          => $txnDate,
                'description'       => $desc,
            ];
        });
    }
}