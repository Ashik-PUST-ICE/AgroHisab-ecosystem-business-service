<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Kpi\KpiTargetRequest;
use App\Http\Resources\Business\Kpi\KpiTargetResource;
use App\Models\Business\KpiTarget;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Kpi\KpiService;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function __construct(protected KpiService $kpiService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $targets = $this->kpiService->index($request->only(['user_id', 'period_type', 'status', 'per_page']));

            return ApiResponse::success($targets, 'KPI targets retrieved successfully');
        });
    }

    public function store(KpiTargetRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $target = $this->kpiService->store($request->validated());

            return ApiResponse::success(new KpiTargetResource($target), 'KPI target created successfully', 201);
        });
    }

    public function show(KpiTarget $kpiTarget)
    {
        return $this->handleRequest(function () use ($kpiTarget) {
            $target = $this->kpiService->show($kpiTarget);

            return ApiResponse::success(new KpiTargetResource($target), 'KPI target retrieved successfully');
        });
    }

    public function update(KpiTargetRequest $request, KpiTarget $kpiTarget)
    {
        return $this->handleRequest(function () use ($request, $kpiTarget) {
            $target = $this->kpiService->update($kpiTarget, $request->validated());

            return ApiResponse::success(new KpiTargetResource($target), 'KPI target updated successfully');
        });
    }

    public function destroy(KpiTarget $kpiTarget)
    {
        return $this->handleRequest(function () use ($kpiTarget) {
            $this->kpiService->destroy($kpiTarget);

            return ApiResponse::success(null, 'KPI target deleted successfully');
        });
    }

    public function summary(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->kpiService->summary($request->only(['user_id', 'period']));

            return ApiResponse::success($data, 'KPI summary retrieved successfully');
        });
    }
}
