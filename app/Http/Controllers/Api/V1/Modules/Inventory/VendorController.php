<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\VendorRequest;
use App\Http\Resources\Modules\Inventory\VendorResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\VendorService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(protected VendorService $vendorService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->vendorService->index($request->all());

            return ApiResponse::success(VendorResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(VendorRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->vendorService->store($request->validated());

            return ApiResponse::success(VendorResource::make($data), 'Vendor Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->vendorService->show($id);

            return ApiResponse::success(VendorResource::make($data));
        });
    }

    public function update(string $id, VendorRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->vendorService->update($id, $request->validated());

            return ApiResponse::success(VendorResource::make($data), 'Vendor Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->vendorService->destroy($id);

            return ApiResponse::success($data, 'Vendor Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->vendorService->toggleStatus($id);

            return ApiResponse::success(VendorResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->vendorService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
