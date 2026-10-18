<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\ExpenseRequest;
use App\Http\Resources\Modules\Finance\ExpenseResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $ExpenseService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->ExpenseService->index($request->all());

            return ApiResponse::success(ExpenseResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(ExpenseRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->ExpenseService->store($request->validated());

            return ApiResponse::success(ExpenseResource::make($data), 'Expense Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->ExpenseService->show($id, ['transactions', 'account', 'financeCategory']);

            return ApiResponse::success(ExpenseResource::make($data));
        });
    }

    public function update(string $id, ExpenseRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->ExpenseService->update($id, $request->validated());

            return ApiResponse::success(ExpenseResource::make($data), 'Expense Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->ExpenseService->destroy($id);

            return ApiResponse::success($data, 'Expense Deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->ExpenseService->toggleStatus($id);

            return ApiResponse::success(ExpenseResource::make($data), 'Status toggled successfully');
        });
    }
}
