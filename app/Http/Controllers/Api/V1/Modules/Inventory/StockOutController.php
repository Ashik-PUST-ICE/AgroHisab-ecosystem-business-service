<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\StockOutService;
use App\Http\Requests\Modules\Inventory\StockOutRequest;
use App\Http\Resources\Modules\Inventory\StockOutResource;
use App\Http\Resources\Modules\Inventory\Purchases\StockHistoryResource;

class StockOutController extends Controller
{
    public function __construct(protected StockOutService $stockOutService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockOutService->index($request->all());

            return ApiResponse::success(StockOutResource::collection($data->load(['stockCategory', 'stockProduct', 'stockHistory', 'brand', 'unit'])), 'Data fetched successfully');
        });
    }

    public function store(StockOutRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockOutService->store($request->validated());

            return ApiResponse::success(StockOutResource::make($data), 'Stock out created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stockOutService->show($id, ['stockCategory', 'stockProduct', 'stockHistory', 'brand', 'unit']);

            return ApiResponse::success(StockOutResource::make($data));
        });
    }

     public function stockHistory(Request $request, string $networkId)
    {
        return $this->handleRequest(function () use ($networkId) {
            $data = $this->stockOutService->stockHistory($networkId);

            return ApiResponse::success(StockHistoryResource::collection($data), 'Stock history data fetched successfully');
        });
    }
}
