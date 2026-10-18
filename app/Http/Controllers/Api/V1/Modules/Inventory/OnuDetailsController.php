<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\StockCustomerOnuRequest;
use App\Http\Resources\Modules\Inventory\StockCustomerOnuResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\OnuDetailsService;
use Illuminate\Http\Request;

class OnuDetailsController extends Controller
{
    public function __construct(protected OnuDetailsService $onuDetailsService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->onuDetailsService->index($request->all());

            return ApiResponse::success(($data), 'Data fetched successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->onuDetailsService->show($id);

            return ApiResponse::success(($data), 'Data fetched successfully');
        });
    }

    public function store(StockCustomerOnuRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->onuDetailsService->store($request->validated());

            return ApiResponse::success(StockCustomerOnuResource::make($data), 'ONU details created successfully', 201);
        });
    }

    public function getAvailableOnuList(Request $request)
    {
        return $this->handleRequest(function () {
            $data = $this->onuDetailsService->getAvailableOnuList();

            return ApiResponse::success(($data), 'List fetched successfully');
        });
    }
}
