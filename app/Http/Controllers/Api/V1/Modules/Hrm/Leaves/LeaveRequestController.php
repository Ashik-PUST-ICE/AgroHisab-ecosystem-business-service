<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Leaves\LeaveRequestRequest;
use App\Http\Resources\Modules\Hrm\Leaves\LeaveRequestResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Leaves\LeaveRequestService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(protected LeaveRequestService $leaveRequestService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveRequestService->index($request->all());

            return ApiResponse::success(LeaveRequestResource::collection($data));
        });
    }

    public function store(LeaveRequestRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveRequestService->store($request->validated());

            return ApiResponse::success(LeaveRequestResource::make($data), 'Leave request created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leaveRequestService->show($id, ['leaveType', 'fiscalYear']);

            return ApiResponse::success(LeaveRequestResource::make($data));
        });
    }

    public function update(LeaveRequestRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->leaveRequestService->update($id, $request->validated());

            return ApiResponse::success(LeaveRequestResource::make($data), 'Leave request updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->leaveRequestService->destroy($id);

            return ApiResponse::success([], 'Leave request deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leaveRequestService->status($id);

            return ApiResponse::success(LeaveRequestResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->leaveRequestService->list();

            return ApiResponse::success($data);
        });
    }
}
