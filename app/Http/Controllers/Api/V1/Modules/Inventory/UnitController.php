<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\UnitRequest;
use App\Http\Resources\Modules\Inventory\UnitResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(protected UnitService $unitService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->unitService->index($request->all());

            return ApiResponse::success(UnitResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(UnitRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->unitService->store($request->validated());

            return ApiResponse::success(UnitResource::make($data), 'Unit Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->unitService->show($id);

            return ApiResponse::success(UnitResource::make($data));
        });
    }

    public function update(string $id, UnitRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->unitService->update($id, $request->validated());

            return ApiResponse::success(UnitResource::make($data), 'Unit Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->unitService->destroy($id);

            return ApiResponse::success($data, 'Unit Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->unitService->toggleStatus($id);

            return ApiResponse::success(UnitResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->unitService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
