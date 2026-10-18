<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () {
            $data = $this->dashboardService->index();

            return ApiResponse::success($data, 'Dashboard data retrieved successfully');
        });
    }
}
