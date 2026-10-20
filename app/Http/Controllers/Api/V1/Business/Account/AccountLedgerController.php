<?php

namespace App\Http\Controllers\Api\V1\Business\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Account\AccountTransferRequest;
use App\Models\Business\Account;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Account\AccountLedgerService;
use Illuminate\Http\Request;

class AccountLedgerController extends Controller
{
    public function __construct(protected AccountLedgerService $ledgerService) {}

    /**
     * GET /api/v1/accounts/{account}/ledger
     * Full account ledger with running balance.
     * Query: from_date, to_date
     */
    public function ledger(Request $request, Account $account)
    {
        return $this->handleRequest(function () use ($request, $account) {
            $data = $this->ledgerService->ledger(
                $account,
                $request->only(['from_date', 'to_date'])
            );
            return ApiResponse::success($data, 'Account ledger retrieved successfully');
        });
    }

    /**
     * POST /api/v1/accounts/transfer
     * Internal transfer between two accounts.
     * Spec: "Internal transfer must not be counted as income or expense."
     */
    public function transfer(AccountTransferRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->ledgerService->transfer($request->validated());
            return ApiResponse::success($data, 'Internal transfer completed successfully', 201);
        });
    }
}