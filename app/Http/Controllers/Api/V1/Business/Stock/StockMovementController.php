<?php

namespace App\Http\Controllers\Api\V1\Business\Stock;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Stock\StockMovementResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Stock\StockMovementService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(protected StockMovementService $stockMovementService) {}

    /**
     * GET /api/v1/stock-movements
     * Paginated stock movement ledger.
     * Query: product_id, type, reference_type, reference_id, from_date, to_date, per_page
     */
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $movements = $this->stockMovementService->list(
                $request->only(['product_id', 'type', 'reference_type', 'reference_id', 'from_date', 'to_date', 'per_page'])
            );
            return ApiResponse::success(StockMovementResource::collection($movements), 'Stock movements retrieved successfully');
        });
    }

    /**
     * GET /api/v1/stock-movements/balance
     * Current stock balance per product (net of all movements).
     * Query: product_id
     */
    public function balance(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $balance = $this->stockMovementService->stockBalance(
                $request->only(['product_id'])
            );
            return ApiResponse::success($balance, 'Stock balance retrieved successfully');
        });
    }
}