<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\EmployeeLoanRequest;
use App\Http\Resources\Modules\Hrm\EmployeeLoanResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\EmployeeLoanService;
use Illuminate\Http\Request;

class EmployeeLoanController extends Controller
{
    public function __construct(protected EmployeeLoanService $employeeLoanService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->employeeLoanService->index($request->all());

            return ApiResponse::success(EmployeeLoanResource::collection($data));
        });
    }

    public function store(EmployeeLoanRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->employeeLoanService->store($request->validated());

            return ApiResponse::success(EmployeeLoanResource::make($data), 'Employee loan created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->employeeLoanService->show($id);

            return ApiResponse::success(EmployeeLoanResource::make($data));
        });
    }

    public function update(EmployeeLoanRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->employeeLoanService->update($id, $request->validated());

            return ApiResponse::success(EmployeeLoanResource::make($data), 'Employee loan updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->employeeLoanService->destroy($id);

            return ApiResponse::success([], 'Employee loan deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->employeeLoanService->status($id);

            return ApiResponse::success(EmployeeLoanResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->employeeLoanService->list();

            return ApiResponse::success($data);
        });
    }
}
