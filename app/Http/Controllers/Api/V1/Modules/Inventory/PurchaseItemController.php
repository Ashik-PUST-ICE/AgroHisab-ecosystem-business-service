<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\PurchaseItemRequest;
use App\Http\Resources\Modules\Inventory\PurchaseItemResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\PurchaseItemService;
use Illuminate\Http\Request;

class PurchaseItemController extends Controller
{
    public function __construct(protected PurchaseItemService $PurchaseItemService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->PurchaseItemService->index($request->all());

            return ApiResponse::success(PurchaseItemResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(PurchaseItemRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->PurchaseItemService->store($request->validated());

            return ApiResponse::success(PurchaseItemResource::make($data), 'Purchase Item Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->PurchaseItemService->show($id, ['purchase', 'brand']);

            return ApiResponse::success(PurchaseItemResource::make($data));
        });
    }

    public function update(string $id, PurchaseItemRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->PurchaseItemService->update($id, $request->validated());

            return ApiResponse::success(PurchaseItemResource::make($data), 'Purchase Item Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->PurchaseItemService->destroy($id);

            return ApiResponse::success($data, 'Purchase Item Deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->PurchaseItemService->toggleStatus($id);

            return ApiResponse::success(PurchaseItemResource::make($data), 'Status toggled successfully');
        });
    }
}
