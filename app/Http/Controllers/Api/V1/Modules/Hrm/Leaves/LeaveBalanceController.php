<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Leaves\LeaveBalanceRequest;
use App\Http\Resources\Modules\Hrm\Leaves\LeaveBalanceResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Leaves\LeaveBalanceService;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function __construct(protected LeaveBalanceService $leaveBalanceService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveBalanceService->index($request->all());

            return ApiResponse::success(LeaveBalanceResource::collection($data));
        });
    }

    public function store(LeaveBalanceRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leaveBalanceService->store($request->validated());

            return ApiResponse::success(LeaveBalanceResource::make($data), 'Leave balance created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leaveBalanceService->show($id, ['user', 'leaveType', 'fiscalYear']);

            return ApiResponse::success(LeaveBalanceResource::make($data));
        });
    }

    public function update(LeaveBalanceRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->leaveBalanceService->update($id, $request->validated());

            return ApiResponse::success(LeaveBalanceResource::make($data), 'Leave balance updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->leaveBalanceService->destroy($id);

            return ApiResponse::success([], 'Leave balance deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->leaveBalanceService->list();

            return ApiResponse::success($data);
        });
    }
}
