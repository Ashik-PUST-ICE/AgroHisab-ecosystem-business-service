<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\ShiftAssignmentRequest;
use App\Http\Resources\Modules\Hrm\ShiftAssignmentResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\ShiftAssignmentService;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    public function __construct(protected ShiftAssignmentService $shiftAssignmentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->shiftAssignmentService->index($request->all());

            return ApiResponse::success(ShiftAssignmentResource::collection($data));
        });
    }

    public function store(ShiftAssignmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->shiftAssignmentService->store($request->validated());

            return ApiResponse::success(ShiftAssignmentResource::make($data), 'Shift assignment created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->shiftAssignmentService->show($id, ['shift']);

            return ApiResponse::success(ShiftAssignmentResource::make($data));
        });
    }

    public function update(ShiftAssignmentRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->shiftAssignmentService->update($id, $request->validated());

            return ApiResponse::success(ShiftAssignmentResource::make($data), 'Shift assignment updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->shiftAssignmentService->destroy($id);

            return ApiResponse::success([], 'Shift assignment deleted successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->shiftAssignmentService->list();

            return ApiResponse::success($data);
        });
    }
}
