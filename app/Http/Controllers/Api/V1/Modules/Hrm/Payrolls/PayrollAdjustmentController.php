<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Payrolls;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Payrolls\PayrollAdjustmentRequest;
use App\Http\Resources\Modules\Hrm\Payrolls\PayrollAdjustmentResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Payrolls\PayrollAdjustmentService;
use Illuminate\Http\Request;

class PayrollAdjustmentController extends Controller
{
    public function __construct(protected PayrollAdjustmentService $payrollAdjustmentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payrollAdjustmentService->index($request->all());

            return ApiResponse::success(PayrollAdjustmentResource::collection($data));
        });
    }

    public function store(PayrollAdjustmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payrollAdjustmentService->store($request->validated());

            return ApiResponse::success(PayrollAdjustmentResource::make($data), 'Payroll adjustment created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payrollAdjustmentService->show($id, ['payslip', 'payrollRun']);

            return ApiResponse::success(PayrollAdjustmentResource::make($data));
        });
    }

    public function update(PayrollAdjustmentRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->payrollAdjustmentService->update($id, $request->validated());

            return ApiResponse::success(PayrollAdjustmentResource::make($data), 'Payroll adjustment updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->payrollAdjustmentService->destroy($id);

            return ApiResponse::success([], 'Payroll adjustment deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->payrollAdjustmentService->list();

            return ApiResponse::success($data);
        });
    }
}
