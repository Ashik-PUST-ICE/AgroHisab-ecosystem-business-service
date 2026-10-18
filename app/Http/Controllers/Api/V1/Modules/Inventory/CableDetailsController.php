<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\CableDetailsRequest;
use App\Http\Resources\Modules\Inventory\CableDetailsResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\CableDetailsService;

class CableDetailsController extends Controller
{
    public function __construct(protected CableDetailsService $cableDetailsService) {}

    public function getFiberProducts()
    {
        return $this->handleRequest(function () {
            $data = $this->cableDetailsService->getFiberProducts();

            return ApiResponse::success($data, 'Fiber products fetched successfully');
        });
    }

    public function getProductDetails($productId)
    {
        return $this->handleRequest(function () use ($productId) {
            $data = $this->cableDetailsService->getProductDetails($productId);

            return ApiResponse::success($data, 'Product details fetched successfully');
        });
    }

    public function store(CableDetailsRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->cableDetailsService->store($request->validated());

            return ApiResponse::success(CableDetailsResource::make($data), 'Cable details saved successfully');
        });
    }
}
