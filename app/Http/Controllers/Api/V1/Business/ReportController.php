<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Report\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function visits(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->reportService->visits($request->only(['from', 'to', 'user_id', 'outlet_id']));

            return ApiResponse::success($data, 'Visit report retrieved successfully');
        });
    }

    public function orders(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->reportService->orders($request->only(['from', 'to', 'user_id', 'outlet_id']));

            return ApiResponse::success($data, 'Order report retrieved successfully');
        });
    }

    public function officerPerformance(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->reportService->officerPerformance($request->only(['from', 'to']));

            return ApiResponse::success($data, 'Officer performance report retrieved successfully');
        });
    }
}
