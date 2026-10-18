<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Payrolls;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Payrolls\PayslipRequest;
use App\Http\Resources\Modules\Hrm\Payrolls\PayslipResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Payrolls\PayslipService;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function __construct(protected PayslipService $payslipService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payslipService->index($request->all());

            return ApiResponse::success(PayslipResource::collection($data));
        });
    }

    public function store(PayslipRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payslipService->store($request->validated());

            return ApiResponse::success(PayslipResource::make($data), 'Payslip created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payslipService->show($id, ['payrollRun', 'payslipLines']);

            return ApiResponse::success(PayslipResource::make($data));
        });
    }

    public function update(PayslipRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->payslipService->update($id, $request->validated());

            return ApiResponse::success(PayslipResource::make($data), 'Payslip updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->payslipService->destroy($id);

            return ApiResponse::success([], 'Payslip deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payslipService->status($id);

            return ApiResponse::success(PayslipResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->payslipService->list();

            return ApiResponse::success($data);
        });
    }
}
