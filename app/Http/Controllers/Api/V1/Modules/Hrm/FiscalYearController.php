<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\FiscalYearRequest;
use App\Http\Resources\Modules\Hrm\FiscalYearResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\FiscalYearService;
use Illuminate\Http\Request;

class FiscalYearController extends Controller
{
    public function __construct(protected FiscalYearService $fiscalYearService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->fiscalYearService->index($request->all());

            return ApiResponse::success(FiscalYearResource::collection($data));
        });
    }

    public function store(FiscalYearRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->fiscalYearService->store($request->validated());

            return ApiResponse::success(FiscalYearResource::make($data), 'Fiscal year created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->fiscalYearService->show($id);

            return ApiResponse::success(FiscalYearResource::make($data));
        });
    }

    public function update(FiscalYearRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->fiscalYearService->update($id, $request->validated());

            return ApiResponse::success(FiscalYearResource::make($data), 'Fiscal year updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->fiscalYearService->destroy($id);

            return ApiResponse::success([], 'Fiscal year deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->fiscalYearService->status($id);

            return ApiResponse::success(FiscalYearResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->fiscalYearService->list();

            return ApiResponse::success($data);
        });
    }
}
