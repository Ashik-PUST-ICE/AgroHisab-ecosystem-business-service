<?php

namespace App\Http\Controllers\Api\V1\Business\ProductCategory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Product\ProductCategoryResource;
use App\Http\Requests\Business\Product\ProductCategoryRequest;
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
            $categories = $this->categoryService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($categories, 'Product categories retrieved successfully');
        });
    }

    public function store(ProductCategoryRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $category = $this->categoryService->store($request->validated());

            return ApiResponse::success(new ProductCategoryResource($category), 'Product category created successfully', 201);
        });
    }

    public function show(ProductCategory $category)
    {
        return $this->handleRequest(function () use ($category) {
            $category = $this->categoryService->show($category);

            return ApiResponse::success(new ProductCategoryResource($category), 'Product category retrieved successfully');
        });
    }

    public function update(ProductCategoryRequest $request, ProductCategory $category)
    {
        return $this->handleRequest(function () use ($request, $category) {
            $category = $this->categoryService->update($category, $request->validated());

            return ApiResponse::success(new ProductCategoryResource($category), 'Product category updated successfully');
        });
    }

    public function destroy(ProductCategory $category)
    {
        return $this->handleRequest(function () use ($category) {
            $this->categoryService->destroy($category);

            return ApiResponse::success(null, 'Product category deleted successfully');
        });
    }
}
