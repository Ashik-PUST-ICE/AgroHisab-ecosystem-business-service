<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Leaves\LeaveTypeRequest;
use App\Http\Resources\Modules\Hrm\Leaves\LeaveTypeResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Leaves\LeaveTypeService;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(protected LeaveTypeService $leaveTypeService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveTypeService->index($request->all());

            return ApiResponse::success(LeaveTypeResource::collection($data));
        });
    }

    public function store(LeaveTypeRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveTypeService->store($request->validated());

            return ApiResponse::success(LeaveTypeResource::make($data), 'Leave type created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leaveTypeService->show($id);

            return ApiResponse::success(LeaveTypeResource::make($data));
        });
    }

    public function update(LeaveTypeRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->leaveTypeService->update($id, $request->validated());

            return ApiResponse::success(LeaveTypeResource::make($data), 'Leave type updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->leaveTypeService->destroy($id);

            return ApiResponse::success([], 'Leave type deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->leaveTypeService->list();

            return ApiResponse::success($data);
        });
    }
}
