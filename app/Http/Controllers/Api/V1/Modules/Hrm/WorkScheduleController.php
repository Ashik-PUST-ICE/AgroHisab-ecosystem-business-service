<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\WorkScheduleRequest;
use App\Http\Resources\Modules\Hrm\WorkScheduleResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\WorkScheduleService;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function __construct(protected WorkScheduleService $workScheduleService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->workScheduleService->index($request->all());

            return ApiResponse::success(WorkScheduleResource::collection($data));
        });
    }

    public function store(WorkScheduleRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->workScheduleService->store($request->validated());

            return ApiResponse::success(WorkScheduleResource::make($data), 'Work schedule created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->workScheduleService->show($id);

            return ApiResponse::success(WorkScheduleResource::make($data));
        });
    }

    public function update(WorkScheduleRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->workScheduleService->update($id, $request->validated());

            return ApiResponse::success(WorkScheduleResource::make($data), 'Work schedule updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->workScheduleService->destroy($id);

            return ApiResponse::success([], 'Work schedule deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->workScheduleService->status($id);

            return ApiResponse::success(WorkScheduleResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->workScheduleService->list();

            return ApiResponse::success($data);
        });
    }
}
