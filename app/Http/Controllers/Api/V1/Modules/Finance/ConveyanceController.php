<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\ConveyanceRequest;
use App\Http\Resources\Modules\Finance\ConveyanceResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\ConveyanceService;
use Illuminate\Http\Request;

class ConveyanceController extends Controller
{
    public function __construct(protected ConveyanceService $ConveyanceService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->ConveyanceService->index($request->all());

            return ApiResponse::success(ConveyanceResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(ConveyanceRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->ConveyanceService->store($request->validated());

            return ApiResponse::success(ConveyanceResource::make($data), 'Conveyance Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->ConveyanceService->show($id, ['transactions']);

            return ApiResponse::success(ConveyanceResource::make($data));
        });
    }

    public function update(string $id, ConveyanceRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->ConveyanceService->update($id, $request->validated());

            return ApiResponse::success(ConveyanceResource::make($data), 'Conveyance Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->ConveyanceService->destroy($id);

            return ApiResponse::success($data, 'Conveyance Deleted successfully');
        });
    }
}
