<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Payrolls;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Payrolls\SalaryComponentRequest;
use App\Http\Resources\Modules\Hrm\Payrolls\SalaryComponentResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Payrolls\SalaryComponentService;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function __construct(protected SalaryComponentService $salaryComponentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->salaryComponentService->index($request->all());

            return ApiResponse::success(SalaryComponentResource::collection($data));
        });
    }

    public function store(SalaryComponentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->salaryComponentService->store($request->validated());

            return ApiResponse::success(SalaryComponentResource::make($data), 'Salary component created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->salaryComponentService->show($id, ['payElement']);

            return ApiResponse::success(SalaryComponentResource::make($data));
        });
    }

    public function update(SalaryComponentRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->salaryComponentService->update($id, $request->validated());

            return ApiResponse::success(SalaryComponentResource::make($data), 'Salary component updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->salaryComponentService->destroy($id);

            return ApiResponse::success([], 'Salary component deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->salaryComponentService->list();

            return ApiResponse::success($data);
        });
    }
}
