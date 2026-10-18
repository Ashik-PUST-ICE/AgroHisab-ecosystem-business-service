<?php

namespace App\Http\Controllers\Api\V1\Business\OutletAssignment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Outlet\OutletAssignmentResource;
use App\Http\Requests\Business\Outlet\OutletAssignmentRequest;
use App\Models\Business\OutletAssignment;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Outlet\OutletAssignmentService;
use Illuminate\Http\Request;

class OutletAssignmentController extends Controller
{
    public function __construct(protected OutletAssignmentService $assignmentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $assignments = $this->assignmentService->index($request->only(['outlet_id', 'user_id', 'per_page']));

            return ApiResponse::success($assignments, 'Outlet assignments retrieved successfully');
        });
    }

    public function store(OutletAssignmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $assignment = $this->assignmentService->store($request->validated());

            return ApiResponse::success(new OutletAssignmentResource($assignment), 'Outlet assigned successfully', 201);
        });
    }

    public function show(OutletAssignment $assignment)
    {
        return $this->handleRequest(function () use ($assignment) {
            $assignment = $this->assignmentService->show($assignment);

            return ApiResponse::success(new OutletAssignmentResource($assignment), 'Assignment retrieved successfully');
        });
    }

    public function update(OutletAssignmentRequest $request, OutletAssignment $assignment)
    {
        return $this->handleRequest(function () use ($request, $assignment) {
            $assignment = $this->assignmentService->update($assignment, $request->validated());

            return ApiResponse::success(new OutletAssignmentResource($assignment), 'Assignment updated successfully');
        });
    }

    public function destroy(OutletAssignment $assignment)
    {
        return $this->handleRequest(function () use ($assignment) {
            $this->assignmentService->destroy($assignment);

            return ApiResponse::success(null, 'Assignment removed successfully');
        });
    }
}
