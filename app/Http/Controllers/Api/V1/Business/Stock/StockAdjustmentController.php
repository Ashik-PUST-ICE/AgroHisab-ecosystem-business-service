<?php

namespace App\Http\Controllers\Api\V1\Business\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Stock\StockAdjustmentRequest;
use App\Http\Resources\Business\Stock\StockAdjustmentResource;
use App\Models\Business\StockAdjustment;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Stock\StockAdjustmentService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function __construct(protected StockAdjustmentService $adjustmentService) {}

    /**
     * GET /api/v1/stock-adjustments
     * List stock adjustments and loss records.
     */
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $adjustments = $this->adjustmentService->list(
                $request->only(['product_id', 'loss_type', 'market_id', 'from_date', 'to_date', 'per_page'])
            );
            return ApiResponse::success(StockAdjustmentResource::collection($adjustments), 'Stock adjustments retrieved successfully');
        });
    }

    /**
     * POST /api/v1/stock-adjustments
     * Record a stock loss, spoilage, shrinkage, or adjustment.
     */
    public function store(StockAdjustmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $userId = function_exists('auth_user_id') ? auth_user_id() : null;
            $adjustment = $this->adjustmentService->create($request->validated(), $userId);
            return ApiResponse::success(new StockAdjustmentResource($adjustment), 'Stock loss/adjustment recorded successfully', 201);
        });
    }

    /**
     * GET /api/v1/stock-adjustments/{stockAdjustment}
     */
    public function show(StockAdjustment $stockAdjustment)
    {
        return $this->handleRequest(function () use ($stockAdjustment) {
            $adjustment = $this->adjustmentService->find($stockAdjustment->id);
            return ApiResponse::success(new StockAdjustmentResource($adjustment), 'Stock adjustment retrieved successfully');
        });
    }

    /**
     * DELETE /api/v1/stock-adjustments/{stockAdjustment}
     */
    public function destroy(StockAdjustment $stockAdjustment)
    {
        return $this->handleRequest(function () use ($stockAdjustment) {
            $this->adjustmentService->delete($stockAdjustment);
            return ApiResponse::success(null, 'Stock adjustment deleted and inventory reverted successfully');
        });
    }
}