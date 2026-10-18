<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\StockReportService;
use Illuminate\Http\Request;

class StockReportController extends Controller
{
    public function __construct(protected StockReportService $stockReportService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $stockReports = $this->stockReportService->index($request->all());

            return ApiResponse::success($stockReports->load(['stockProduct', 'stockCategory', 'vendor', 'brand', 'unit']), 'Stock reports retrieved successfully');
        });
    }
}
