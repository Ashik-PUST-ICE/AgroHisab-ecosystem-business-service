<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Holidays;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Holidays\HolidayGroupRequest;
use App\Http\Resources\Modules\Hrm\Holidays\HolidayGroupResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Holidays\HolidayGroupService;
use Illuminate\Http\Request;

class HolidayGroupController extends Controller
{
    public function __construct(protected HolidayGroupService $holidayGroupService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->holidayGroupService->index($request->all());

            return ApiResponse::success(HolidayGroupResource::collection($data));
        });
    }

    public function store(HolidayGroupRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->holidayGroupService->store($request->validated());

            return ApiResponse::success(HolidayGroupResource::make($data), 'Holiday group created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->holidayGroupService->show($id);

            return ApiResponse::success(HolidayGroupResource::make($data));
        });
    }

    public function update(HolidayGroupRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->holidayGroupService->update($id, $request->validated());

            return ApiResponse::success(HolidayGroupResource::make($data), 'Holiday group updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->holidayGroupService->destroy($id);

            return ApiResponse::success([], 'Holiday group deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->holidayGroupService->status($id);

            return ApiResponse::success(HolidayGroupResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->holidayGroupService->list();

            return ApiResponse::success($data);
        });
    }
}
