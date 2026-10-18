<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Payrolls;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Payrolls\PayslipLineRequest;
use App\Http\Resources\Modules\Hrm\Payrolls\PayslipLineResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Payrolls\PayslipLineService;
use Illuminate\Http\Request;

class PayslipLineController extends Controller
{
    public function __construct(protected PayslipLineService $payslipLineService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payslipLineService->index($request->all());

            return ApiResponse::success(PayslipLineResource::collection($data));
        });
    }

    public function store(PayslipLineRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->payslipLineService->store($request->validated());

            return ApiResponse::success(PayslipLineResource::make($data), 'Payslip line created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->payslipLineService->show($id, ['payElement', 'payslip']);

            return ApiResponse::success(PayslipLineResource::make($data));
        });
    }

    public function update(PayslipLineRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->payslipLineService->update($id, $request->validated());

            return ApiResponse::success(PayslipLineResource::make($data), 'Payslip line updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->payslipLineService->destroy($id);

            return ApiResponse::success([], 'Payslip line deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->payslipLineService->list();

            return ApiResponse::success($data);
        });
    }
}
