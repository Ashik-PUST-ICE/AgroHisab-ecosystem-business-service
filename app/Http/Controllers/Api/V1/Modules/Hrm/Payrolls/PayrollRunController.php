<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Payrolls;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Payrolls\PayrollRunRequest;
use App\Http\Resources\Modules\Hrm\Payrolls\PayrollRunResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Payrolls\PayrollRunService;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function __construct(protected PayrollRunService $payrollRunService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payrollRunService->index($request->all());

            return ApiResponse::success(PayrollRunResource::collection($data));
        });
    }

    public function store(PayrollRunRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payrollRunService->store($request->validated());

            return ApiResponse::success(PayrollRunResource::make($data), 'Payroll run created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payrollRunService->show($id, ['payslips']);

            return ApiResponse::success(PayrollRunResource::make($data));
        });
    }

    public function update(PayrollRunRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->payrollRunService->update($id, $request->validated());

            return ApiResponse::success(PayrollRunResource::make($data), 'Payroll run updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->payrollRunService->destroy($id);

            return ApiResponse::success([], 'Payroll run deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->payrollRunService->list();

            return ApiResponse::success($data);
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payrollRunService->status($id);

            return ApiResponse::success(PayrollRunResource::make($data), 'Status toggled successfully');
        });
    }
}
