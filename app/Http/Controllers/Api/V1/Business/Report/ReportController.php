<?php

namespace App\Http\Controllers\Api\V1\Business\Report;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Report\ExtendedReportService;
use App\Services\Business\Report\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected ExtendedReportService $extendedReportService
    ) {}

    // ── Existing Reports ────────────────────────────────────────────────────

    public function profitability(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $report = $this->reportService->getProfitabilityReport(
                $request->only(['from_date', 'to_date', 'aratdar_id', 'market_id', 'status'])
            );
            return ApiResponse::success($report, 'Consignment profitability report generated successfully');
        });
    }

    public function cashbook(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $cashbook = $this->reportService->getCashbookReport(
                $request->only(['account_id', 'from_date', 'to_date'])
            );
            return ApiResponse::success($cashbook, 'Cashbook statement generated successfully');
        });
    }

    public function inventoryValuation(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $inventory = $this->reportService->getInventoryValuationReport(
                $request->only(['product_id', 'category_id', 'as_of_date'])
            );
            return ApiResponse::success($inventory, 'Inventory valuation report generated successfully');
        });
    }

    // ── New Extended Reports ─────────────────────────────────────────────────

    /**
     * GET /api/v1/reports/receivables
     * Aratdar outstanding receivables aging (0-30, 31-60, 61-90, 90+ days).
     * Spec §3.14: "Receivables/payables aging"
     */
    public function receivables(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $report = $this->extendedReportService->receivablesAging(
                $request->only(['aratdar_id'])
            );
            return ApiResponse::success($report, 'Receivables aging report generated successfully');
        });
    }

    /**
     * GET /api/v1/reports/payables
     * Supplier outstanding payables aging (0-30, 31-60, 61-90, 90+ days).
     * Spec §3.14: "Receivables/payables aging"
     */
    public function payables(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $report = $this->extendedReportService->payablesAging(
                $request->only(['supplier_id'])
            );
            return ApiResponse::success($report, 'Payables aging report generated successfully');
        });
    }

    /**
     * GET /api/v1/reports/sales-by-product
     * Sales summary grouped by product. Filterable by date range, product, aratdar.
     * Spec §3.14: "Sales and purchases by date, product, market, supplier, and aratdar"
     */
    public function salesByProduct(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $report = $this->extendedReportService->salesByProduct(
                $request->only(['product_id', 'from_date', 'to_date', 'aratdar_id'])
            );
            return ApiResponse::success($report, 'Sales by product report generated successfully');
        });
    }
}