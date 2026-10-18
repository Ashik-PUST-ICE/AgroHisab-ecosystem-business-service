<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Product\ProductResource;
use App\Http\Requests\Business\Product\ProductRequest;
use App\Models\Business\Product;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $products = $this->productService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($products, 'Products retrieved successfully');
        });
    }

    public function store(ProductRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $product = $this->productService->store($request->validated());

            return ApiResponse::success(new ProductResource($product), 'Product created successfully', 201);
        });
    }

    public function show(Product $product)
    {
        return $this->handleRequest(function () use ($product) {
            $product = $this->productService->show($product);

            return ApiResponse::success(new ProductResource($product), 'Product retrieved successfully');
        });
    }

    public function update(ProductRequest $request, Product $product)
    {
        return $this->handleRequest(function () use ($request, $product) {
            $product = $this->productService->update($product, $request->validated());

            return ApiResponse::success(new ProductResource($product), 'Product updated successfully');
        });
    }

    public function destroy(Product $product)
    {
        return $this->handleRequest(function () use ($product) {
            $this->productService->destroy($product);

            return ApiResponse::success(null, 'Product deleted successfully');
        });
    }
}
