<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\AccountRequest;
use App\Http\Resources\Modules\Finance\AccountResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(protected AccountService $AccountService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->AccountService->index($request->all());

            return ApiResponse::success(AccountResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(AccountRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->AccountService->store($request->validated());

            return ApiResponse::success(AccountResource::make($data), 'Account Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->AccountService->show($id);

            return ApiResponse::success(AccountResource::make($data));
        });
    }

    public function update(string $id, AccountRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->AccountService->update($id, $request->validated());

            return ApiResponse::success(AccountResource::make($data), 'Account Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->AccountService->destroy($id);

            return ApiResponse::success($data, 'Account Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->AccountService->status($id);

            return ApiResponse::success(AccountResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->AccountService->list($request->all());

            return ApiResponse::success($data);
        });
    }
}
