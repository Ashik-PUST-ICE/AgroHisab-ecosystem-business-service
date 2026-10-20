<?php

namespace App\Http\Controllers\Api\V1\Business\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Supplier\SupplierRequest;
use App\Http\Resources\Business\Supplier\SupplierResource;
use App\Models\Business\Supplier;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Supplier\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(protected SupplierService $supplierService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $suppliers = $this->supplierService->index(
                $request->only(['search', 'status', 'district', 'per_page'])
            );

            return ApiResponse::success(SupplierResource::collection($suppliers), 'Suppliers retrieved successfully');
        });
    }

    public function store(SupplierRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $supplier = $this->supplierService->store($request->validated());

            return ApiResponse::success(new SupplierResource($supplier), 'Supplier created successfully', 201);
        });
    }

    public function show(Supplier $supplier)
    {
        return $this->handleRequest(function () use ($supplier) {
            $supplier = $this->supplierService->show($supplier);

            return ApiResponse::success(new SupplierResource($supplier), 'Supplier retrieved successfully');
        });
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        return $this->handleRequest(function () use ($request, $supplier) {
            $supplier = $this->supplierService->update($supplier, $request->validated());

            return ApiResponse::success(new SupplierResource($supplier), 'Supplier updated successfully');
        });
    }

    public function destroy(Supplier $supplier)
    {
        return $this->handleRequest(function () use ($supplier) {
            $this->supplierService->destroy($supplier);

            return ApiResponse::success(null, 'Supplier deleted successfully');
        });
    }
}