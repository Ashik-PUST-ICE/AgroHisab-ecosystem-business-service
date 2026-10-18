<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\BandwidthPurchaseService;
use App\Http\Requests\Modules\Inventory\BandwidthPurchaseRequest;
use App\Http\Resources\Modules\Inventory\BandwidthPurchaseResource;


class BandwidthPurchaseController extends Controller
{
    public function __construct(protected BandwidthPurchaseService $bandwidthPurchaseService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->bandwidthPurchaseService->index($request->all());

            return ApiResponse::success(BandwidthPurchaseResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(BandwidthPurchaseRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->bandwidthPurchaseService->store($request->validated());

            return ApiResponse::success($data, 'Bandwidth Purchase Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $relations = [
                'account:id,account_holder_name',
                'vendor:id,first_name,last_name',
                'items.product:id,name',
                'items.unit:id,name',
            ];

            $data = $this->bandwidthPurchaseService->show($id, $relations);

            return ApiResponse::success(BandwidthPurchaseResource::make($data), 'Data fetched successfully');
        });
    }
}
