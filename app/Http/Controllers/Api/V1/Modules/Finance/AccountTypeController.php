<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\AccountTypeRequest;
use App\Http\Resources\Modules\Finance\AccountTypeResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\AccountTypeService;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    public function __construct(protected AccountTypeService $AccountTypeService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->AccountTypeService->index($request->all());

            return ApiResponse::success(AccountTypeResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(AccountTypeRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->AccountTypeService->store($request->validated());

            return ApiResponse::success(AccountTypeResource::make($data), 'AccountType Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->AccountTypeService->show($id);

            return ApiResponse::success(AccountTypeResource::make($data));
        });
    }

    public function update(string $id, AccountTypeRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->AccountTypeService->update($id, $request->validated());

            return ApiResponse::success(AccountTypeResource::make($data), 'AccountType Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->AccountTypeService->destroy($id);

            return ApiResponse::success($data, 'AccountType Deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->AccountTypeService->toggleStatus($id);

            return ApiResponse::success(AccountTypeResource::make($data), 'Status toggled successfully');
        });
    }
}
