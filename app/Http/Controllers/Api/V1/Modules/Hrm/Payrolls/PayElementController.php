<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Payrolls;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Payrolls\PayElementRequest;
use App\Http\Resources\Modules\Hrm\Payrolls\PayElementResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Payrolls\PayElementService;
use Illuminate\Http\Request;

class PayElementController extends Controller
{
    public function __construct(protected PayElementService $payElementService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payElementService->index($request->all());

            return ApiResponse::success(PayElementResource::collection($data));
        });
    }

    public function store(PayElementRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payElementService->store($request->validated());

            return ApiResponse::success(PayElementResource::make($data), 'Pay element created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payElementService->show($id);

            return ApiResponse::success(PayElementResource::make($data));
        });
    }

    public function update(PayElementRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->payElementService->update($id, $request->validated());

            return ApiResponse::success(PayElementResource::make($data), 'Pay element updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->payElementService->destroy($id);

            return ApiResponse::success([], 'Pay element deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->payElementService->list();

            return ApiResponse::success($data);
        });
    }
}
