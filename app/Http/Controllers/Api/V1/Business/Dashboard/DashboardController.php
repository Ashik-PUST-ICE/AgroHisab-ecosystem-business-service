<?php

namespace App\Http\Controllers\Api\V1\Business\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function metrics()
    {
        return $this->handleRequest(function () {
            $metrics = $this->dashboardService->getMetrics();

            return ApiResponse::success($metrics, 'Dashboard metrics retrieved successfully');
        });
    }
}