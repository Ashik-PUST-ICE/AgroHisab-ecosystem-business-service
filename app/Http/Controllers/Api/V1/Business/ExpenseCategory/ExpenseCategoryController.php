<?php

namespace App\Http\Controllers\Api\V1\Business\ExpenseCategory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\ExpenseCategory\ExpenseCategoryRequest;
use App\Http\Resources\Business\ExpenseCategory\ExpenseCategoryResource;
use App\Models\Business\ExpenseCategory;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\ExpenseCategory\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct(protected ExpenseCategoryService $expenseCategoryService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $categories = $this->expenseCategoryService->index(
                $request->only(['search', 'status', 'per_page'])
            );

            return ApiResponse::success(ExpenseCategoryResource::collection($categories), 'Expense categories retrieved successfully');
        });
    }

    public function store(ExpenseCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $category = $this->expenseCategoryService->store($request->validated());

            return ApiResponse::success(new ExpenseCategoryResource($category), 'Expense category created successfully', 201);
        });
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return $this->handleRequest(function () use ($expenseCategory) {
            $category = $this->expenseCategoryService->show($expenseCategory);

            return ApiResponse::success(new ExpenseCategoryResource($category), 'Expense category retrieved successfully');
        });
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory)
    {
        return $this->handleRequest(function () use ($request, $expenseCategory) {
            $category = $this->expenseCategoryService->update($expenseCategory, $request->validated());

            return ApiResponse::success(new ExpenseCategoryResource($category), 'Expense category updated successfully');
        });
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        return $this->handleRequest(function () use ($expenseCategory) {
            $this->expenseCategoryService->destroy($expenseCategory);

            return ApiResponse::success(null, 'Expense category deleted successfully');
        });
    }
}