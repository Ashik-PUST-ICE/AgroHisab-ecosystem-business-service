<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\BrandRequest;
use App\Http\Resources\Modules\Inventory\BrandResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brandService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->brandService->index($request->all());

            return ApiResponse::success(BrandResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(BrandRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->brandService->store($request->validated());

            return ApiResponse::success(BrandResource::make($data), 'Brand Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->brandService->show($id);

            return ApiResponse::success(BrandResource::make($data));
        });
    }

    public function update(string $id, BrandRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->brandService->update($id, $request->validated());

            return ApiResponse::success(BrandResource::make($data), 'Brand Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->brandService->destroy($id);

            return ApiResponse::success($data, 'Brand Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->brandService->toggleStatus($id);

            return ApiResponse::success(BrandResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->brandService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
