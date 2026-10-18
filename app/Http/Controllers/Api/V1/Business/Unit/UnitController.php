<?php

namespace App\Http\Controllers\Api\V1\Business\Unit;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Product\UnitResource;
use App\Http\Requests\Business\Product\UnitRequest;
use App\Models\Business\Unit;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Product\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(protected UnitService $unitService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $units = $this->unitService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($units, 'Units retrieved successfully');
        });
    }

    public function store(UnitRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $unit = $this->unitService->store($request->validated());

            return ApiResponse::success(new UnitResource($unit), 'Unit created successfully', 201);
        });
    }

    public function show(Unit $unit)
    {
        return $this->handleRequest(function () use ($unit) {
            $unit = $this->unitService->show($unit);

            return ApiResponse::success(new UnitResource($unit), 'Unit retrieved successfully');
        });
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        return $this->handleRequest(function () use ($request, $unit) {
            $unit = $this->unitService->update($unit, $request->validated());

            return ApiResponse::success(new UnitResource($unit), 'Unit updated successfully');
        });
    }

    public function destroy(Unit $unit)
    {
        return $this->handleRequest(function () use ($unit) {
            $this->unitService->destroy($unit);

            return ApiResponse::success(null, 'Unit deleted successfully');
        });
    }
}
