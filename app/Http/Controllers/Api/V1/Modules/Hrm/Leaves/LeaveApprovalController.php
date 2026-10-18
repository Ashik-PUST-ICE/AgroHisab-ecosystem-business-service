<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Leaves\LeaveApprovalRequest;
use App\Http\Resources\Modules\Hrm\Leaves\LeaveApprovalResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Leaves\LeaveApprovalService;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    public function __construct(protected LeaveApprovalService $leaveApprovalService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveApprovalService->index($request->all());

            return ApiResponse::success(LeaveApprovalResource::collection($data));
        });
    }

    public function store(LeaveApprovalRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveApprovalService->store($request->validated());

            return ApiResponse::success(LeaveApprovalResource::make($data), 'Leave approval created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leaveApprovalService->show($id, ['leaveRequest']);

            return ApiResponse::success(LeaveApprovalResource::make($data));
        });
    }

    public function update(LeaveApprovalRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->leaveApprovalService->update($id, $request->validated());

            return ApiResponse::success(LeaveApprovalResource::make($data), 'Leave approval updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->leaveApprovalService->destroy($id);

            return ApiResponse::success([], 'Leave approval deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->leaveApprovalService->list();

            return ApiResponse::success($data);
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leaveApprovalService->status($id);

            return ApiResponse::success(LeaveApprovalResource::make($data), 'Status toggled successfully');
        });
    }
}
