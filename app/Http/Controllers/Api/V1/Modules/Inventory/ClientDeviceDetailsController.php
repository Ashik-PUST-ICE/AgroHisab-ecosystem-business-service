<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\ClientDeviceDetailsRequest;
use App\Http\Resources\Modules\Inventory\ClientDeviceDetailsResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\ClientDeviceDetailsService;
use Illuminate\Http\Request;

class ClientDeviceDetailsController extends Controller
{
    public function __construct(protected ClientDeviceDetailsService $clientDeviceDetailsService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->clientDeviceDetailsService->index($request->all());

            return ApiResponse::success(ClientDeviceDetailsResource::collection($data));
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->clientDeviceDetailsService->show($id);

            return ApiResponse::success(ClientDeviceDetailsResource::make($data));
        });
    }

    public function update(ClientDeviceDetailsRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->clientDeviceDetailsService->update($request->validated());

            return ApiResponse::success(ClientDeviceDetailsResource::make($data), 'Client device details updated successfully');
        });
    }
}
