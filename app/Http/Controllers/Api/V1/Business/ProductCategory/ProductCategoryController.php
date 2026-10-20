<?php

namespace App\Http\Controllers\Api\V1\Business\ProductCategory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Product\ProductCategoryRequest;
use App\Http\Resources\Business\Product\ProductCategoryResource;
use App\Models\Business\ProductCategory;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Product\ProductCategoryService;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct(protected ProductCategoryService $categoryService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $categories = $this->categoryService->index($request->only(['search', 'status', 'per_page']));

            return ApiResponse::success(ProductCategoryResource::collection($categories), 'Product categories retrieved successfully');
        });
    }

    public function store(ProductCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $category = $this->categoryService->store($request->validated());

            return ApiResponse::success(new ProductCategoryResource($category), 'Product category created successfully', 201);
        });
    }

    public function show(ProductCategory $productCategory)
    {
        return $this->handleRequest(function () use ($productCategory) {
            $category = $this->categoryService->show($productCategory);

            return ApiResponse::success(new ProductCategoryResource($category), 'Product category retrieved successfully');
        });
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        return $this->handleRequest(function () use ($request, $productCategory) {
            $category = $this->categoryService->update($productCategory, $request->validated());

            return ApiResponse::success(new ProductCategoryResource($category), 'Product category updated successfully');
        });
    }

    public function destroy(ProductCategory $productCategory)
    {
        return $this->handleRequest(function () use ($productCategory) {
            $this->categoryService->destroy($productCategory);

            return ApiResponse::success(null, 'Product category deleted successfully');
        });
    }
}
