<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\FundTransferRequest;
use App\Http\Resources\Modules\Finance\FundTransferResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\FundTransferService;
use Illuminate\Http\Request;

class FundTransferController extends Controller
{
    public function __construct(protected FundTransferService $FundTransferService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->FundTransferService->index($request->all());

            return ApiResponse::success(FundTransferResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(FundTransferRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->FundTransferService->store($request->validated());

            return ApiResponse::success(FundTransferResource::make($data), 'FundTransfer Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->FundTransferService->show($id);

            return ApiResponse::success(FundTransferResource::make($data));
        });
    }

    public function update(string $id, FundTransferRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->FundTransferService->update($id, $request->validated());

            return ApiResponse::success(FundTransferResource::make($data), 'FundTransfer Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->FundTransferService->destroy($id);

            return ApiResponse::success($data, 'FundTransfer Deleted successfully');
        });
    }
}
