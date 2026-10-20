<?php

namespace App\Http\Controllers\Api\V1\Business\Expense;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Expense\ExpenseRequest;
use App\Http\Resources\Business\Expense\ExpenseResource;
use App\Models\Business\Expense;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Expense\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $expenseService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $expenses = $this->expenseService->list(
                $request->only(['expense_category_id', 'shipment_id', 'account_id', 'from_date', 'to_date', 'search', 'per_page'])
            );

            return ApiResponse::success(ExpenseResource::collection($expenses), 'Expenses retrieved successfully');
        });
    }

    public function store(ExpenseRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $expense = $this->expenseService->store($request->validated());

            return ApiResponse::success(new ExpenseResource($expense), 'Expense recorded successfully', 201);
        });
    }

    public function show(Expense $expense)
    {
        return $this->handleRequest(function () use ($expense) {
            $expense = $this->expenseService->show($expense);

            return ApiResponse::success(new ExpenseResource($expense), 'Expense retrieved successfully');
        });
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        return $this->handleRequest(function () use ($request, $expense) {
            $expense = $this->expenseService->update($expense, $request->validated());

            return ApiResponse::success(new ExpenseResource($expense), 'Expense updated successfully');
        });
    }

    public function destroy(Expense $expense)
    {
        return $this->handleRequest(function () use ($expense) {
            $this->expenseService->destroy($expense);

            return ApiResponse::success(null, 'Expense deleted successfully');
        });
    }
}