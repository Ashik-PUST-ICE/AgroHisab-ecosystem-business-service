<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Leaves;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Leaves\LeavePolicyRequest;
use App\Http\Resources\Modules\Hrm\Leaves\LeavePolicyResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Leaves\LeavePolicyService;
use Illuminate\Http\Request;

class LeavePolicyController extends Controller
{
    public function __construct(protected LeavePolicyService $leavePolicyService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leavePolicyService->index($request->all());

            return ApiResponse::success(LeavePolicyResource::collection($data));
        });
    }

    public function store(LeavePolicyRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->leavePolicyService->store($request->validated());

            return ApiResponse::success(LeavePolicyResource::make($data), 'Leave policy created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->leavePolicyService->show($id, ['fiscalYear', 'leaveType']);

            return ApiResponse::success(LeavePolicyResource::make($data));
        });
    }

    public function update(LeavePolicyRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->leavePolicyService->update($id, $request->validated());

            return ApiResponse::success(LeavePolicyResource::make($data), 'Leave policy updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->leavePolicyService->destroy($id);

            return ApiResponse::success([], 'Leave policy deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->leavePolicyService->list();

            return ApiResponse::success($data);
        });
    }
}
