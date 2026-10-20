<?php

namespace App\Http\Controllers\Api\V1\Business\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Account\AccountRequest;
use App\Http\Resources\Business\Account\AccountResource;
use App\Models\Business\Account;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Account\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(protected AccountService $accountService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $accounts = $this->accountService->index(
                $request->only(['search', 'type', 'status', 'per_page'])
            );

            return ApiResponse::success(AccountResource::collection($accounts), 'Accounts retrieved successfully');
        });
    }

    public function store(AccountRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $account = $this->accountService->store($request->validated());

            return ApiResponse::success(new AccountResource($account), 'Account created successfully', 201);
        });
    }

    public function show(Account $account)
    {
        return $this->handleRequest(function () use ($account) {
            $account = $this->accountService->show($account);

            return ApiResponse::success(new AccountResource($account), 'Account retrieved successfully');
        });
    }

    public function update(AccountRequest $request, Account $account)
    {
        return $this->handleRequest(function () use ($request, $account) {
            $account = $this->accountService->update($account, $request->validated());

            return ApiResponse::success(new AccountResource($account), 'Account updated successfully');
        });
    }

    public function destroy(Account $account)
    {
        return $this->handleRequest(function () use ($account) {
            $this->accountService->destroy($account);

            return ApiResponse::success(null, 'Account deleted successfully');
        });
    }
}