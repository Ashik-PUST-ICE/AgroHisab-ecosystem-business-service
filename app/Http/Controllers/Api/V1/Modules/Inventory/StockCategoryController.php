<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\StockCategoryRequest;
use App\Http\Resources\Modules\Inventory\StockCategoryResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\StockCategoryService;
use Illuminate\Http\Request;

class StockCategoryController extends Controller
{
    public function __construct(protected StockCategoryService $stockCategoryService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockCategoryService->index($request->all());

            return ApiResponse::success(StockCategoryResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(StockCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockCategoryService->store($request->validated());

            return ApiResponse::success(StockCategoryResource::make($data), 'Stock Category Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stockCategoryService->show($id);

            return ApiResponse::success(StockCategoryResource::make($data));
        });
    }

    public function update(string $id, StockCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->stockCategoryService->update($id, $request->validated());

            return ApiResponse::success(StockCategoryResource::make($data), 'Stock Category Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stockCategoryService->destroy($id);

            return ApiResponse::success($data, 'Stock Category Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stockCategoryService->toggleStatus($id);

            return ApiResponse::success(StockCategoryResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockCategoryService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
