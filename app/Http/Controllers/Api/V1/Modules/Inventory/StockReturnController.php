<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\StockReturnRequest;
use App\Http\Resources\Modules\Inventory\StockReturnResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\StockReturnService;
use Illuminate\Http\Request;

class StockReturnController extends Controller
{
    public function __construct(protected StockReturnService $stockReturnService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockReturnService->index($request->all());

            return ApiResponse::success(StockReturnResource::collection($data->load(['stockCategory', 'stockProduct', 'returnProduct', 'unit', 'returnable'])));
        });
    }

    public function store(StockReturnRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockReturnService->store($request->validated());

            return ApiResponse::success(StockReturnResource::make($data), 'Stock return created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stockReturnService->show($id, ['stockCategory', 'stockProduct', 'returnProduct', 'unit', 'returnable']);

            return ApiResponse::success(StockReturnResource::make($data));
        });
    }

    public function getAssignedProductsForReturn(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockReturnService->getAssignedProductsForReturn($request->all());

            return ApiResponse::success($data);
        });
    }

    public function getAvailableProducts(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockReturnService->getAvailableProducts($request->all());

            return ApiResponse::success($data);
        });
    }
}
