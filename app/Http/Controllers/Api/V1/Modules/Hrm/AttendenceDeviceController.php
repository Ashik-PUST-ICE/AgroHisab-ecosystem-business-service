<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\AttendenceDeviceRequest;
use App\Http\Resources\Modules\Hrm\AttendenceDeviceResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\AttendenceDeviceService;
use Illuminate\Http\Request;

class AttendenceDeviceController extends Controller
{
    public function __construct(protected AttendenceDeviceService $attendenceDeviceService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->attendenceDeviceService->index($request->all());

            return ApiResponse::success(AttendenceDeviceResource::collection($data));
        });
    }

    public function store(AttendenceDeviceRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->attendenceDeviceService->store($request->validated());

            return ApiResponse::success(AttendenceDeviceResource::make($data), 'Attendance device created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->attendenceDeviceService->show($id);

            return ApiResponse::success(AttendenceDeviceResource::make($data));
        });
    }

    public function update(AttendenceDeviceRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->attendenceDeviceService->update($id, $request->validated());

            return ApiResponse::success(AttendenceDeviceResource::make($data), 'Attendance device updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->attendenceDeviceService->destroy($id);

            return ApiResponse::success([], 'Attendance device deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->attendenceDeviceService->status($id);

            return ApiResponse::success(AttendenceDeviceResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->attendenceDeviceService->list();

            return ApiResponse::success($data);
        });
    }
}
