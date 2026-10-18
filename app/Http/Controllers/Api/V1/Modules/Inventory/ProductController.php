<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\ProductRequest;
use App\Http\Resources\Modules\Inventory\ProductResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->productService->index($request->all());

            return ApiResponse::success(ProductResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(ProductRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->productService->store($request->validated());

            return ApiResponse::success(ProductResource::make($data), 'Product Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->productService->show($id, ['stockCategory', 'unit']);

            return ApiResponse::success(ProductResource::make($data));
        });
    }

    public function update(string $id, ProductRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->productService->update($id, $request->validated());

            return ApiResponse::success(ProductResource::make($data), 'Product Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->productService->destroy($id);

            return ApiResponse::success($data, 'Product Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->productService->toggleStatus($id);

            return ApiResponse::success(ProductResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->productService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }

    public function getAvailableList(string $productId)
    {
        return $this->handleRequest(function () use ($productId) {
            $data = $this->productService->getAvailableList($productId);

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }
}
