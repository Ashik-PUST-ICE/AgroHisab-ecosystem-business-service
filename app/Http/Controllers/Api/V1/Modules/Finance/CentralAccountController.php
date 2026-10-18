<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\CentralAccountRequest;
use App\Http\Resources\Modules\Finance\CentralAccountResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\CentralAccountService;
use Illuminate\Http\Request;

class CentralAccountController extends Controller
{
    public function __construct(protected CentralAccountService $centralAccountService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->centralAccountService->index($request->all());

            return ApiResponse::success(CentralAccountResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(CentralAccountRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->centralAccountService->store($request->validated());

            return ApiResponse::success(CentralAccountResource::make($data), 'Central account created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->centralAccountService->show($id);

            return ApiResponse::success(CentralAccountResource::make($data));
        });
    }

    public function update(string $id, CentralAccountRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->centralAccountService->update($id, $request->validated());

            return ApiResponse::success(CentralAccountResource::make($data), 'Central account updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->centralAccountService->destroy($id);

            return ApiResponse::success(null, 'Central account deleted successfully');
        });
    }

    public function toggleStatus(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->centralAccountService->toggleStatus($id);

            return ApiResponse::success(CentralAccountResource::make($data), 'Central account status toggled successfully');
        });
    }
}
