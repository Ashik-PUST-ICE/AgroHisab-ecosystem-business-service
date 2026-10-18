<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\FinanceCategoryRequest;
use App\Http\Resources\Modules\Finance\FinanceCategoryResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\FinanceCategoryService;
use Illuminate\Http\Request;

class FinanceCategoryController extends Controller
{
    public function __construct(protected FinanceCategoryService $FinanceCategoryService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->FinanceCategoryService->index($request->all());

            return ApiResponse::success(FinanceCategoryResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(FinanceCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->FinanceCategoryService->store($request->validated());

            return ApiResponse::success(FinanceCategoryResource::make($data), 'FinanceCategory Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->FinanceCategoryService->show($id);

            return ApiResponse::success(FinanceCategoryResource::make($data));
        });
    }

    public function update(string $id, FinanceCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->FinanceCategoryService->update($id, $request->validated());

            return ApiResponse::success(FinanceCategoryResource::make($data), 'FinanceCategory Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->FinanceCategoryService->destroy($id);

            return ApiResponse::success($data, 'FinanceCategory Deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->FinanceCategoryService->toggleStatus($id);

            return ApiResponse::success(FinanceCategoryResource::make($data), 'Status toggled successfully');
        });
    }
}
