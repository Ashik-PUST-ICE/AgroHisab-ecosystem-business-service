<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\StockTransferRequest;
use App\Http\Resources\Modules\Inventory\StockTransferResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\StockTransferService;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function __construct(protected StockTransferService $stockTransferService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockTransferService->index($request->all());

            return ApiResponse::success(StockTransferResource::collection($data->load(['stockCategory', 'stockProduct', 'transferProduct', 'unit'])), 'Data fetched successfully');
        });
    }

    public function store(StockTransferRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->stockTransferService->store($request->validated());

            return ApiResponse::success(StockTransferResource::make($data->load(['stockCategory', 'stockProduct', 'transferProduct', 'unit'])), 'Stock transfer created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->stockTransferService->show($id, ['stockCategory', 'stockProduct', 'transferProduct', 'unit']);

            return ApiResponse::success(StockTransferResource::make($data));
        });
    }

    /**
     * Get available serial numbers/MAC addresses for a product
     */
    public function getAvailableSerials(Request $request, string $productId)
    {
        return $this->handleRequest(function () use ($productId) {
            $serials = $this->stockTransferService->getAvailableSerials((int) $productId);

            return ApiResponse::success($serials, 'Available serials fetched successfully');
        });
    }
}
