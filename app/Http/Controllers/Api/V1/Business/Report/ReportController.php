<?php

namespace App\Http\Controllers\Api\V1\Business\Report;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Report\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function profitability(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $report = $this->reportService->getProfitabilityReport(
                $request->only(['from_date', 'to_date', 'aratdar_id', 'market_id', 'status'])
            );

            return ApiResponse::success($report, 'Consignment profitability report generated successfully');
        });
    }
}