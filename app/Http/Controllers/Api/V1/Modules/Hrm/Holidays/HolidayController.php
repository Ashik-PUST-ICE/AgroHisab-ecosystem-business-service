<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm\Holidays;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\Holidays\HolidayRequest;
use App\Http\Resources\Modules\Hrm\Holidays\HolidayResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\Holidays\HolidayService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function __construct(protected HolidayService $holidayService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->holidayService->index($request->all());

            return ApiResponse::success(HolidayResource::collection($data));
        });
    }

    public function store(HolidayRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->holidayService->store($request->validated());

            return ApiResponse::success(HolidayResource::make($data), 'Holiday created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->holidayService->show($id, ['holidayGroup']);

            return ApiResponse::success(HolidayResource::make($data));
        });
    }

    public function update(HolidayRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->holidayService->update($id, $request->validated());

            return ApiResponse::success(HolidayResource::make($data), 'Holiday updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->holidayService->destroy($id);

            return ApiResponse::success([], 'Holiday deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->holidayService->status($id);

            return ApiResponse::success(HolidayResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->holidayService->list();

            return ApiResponse::success($data);
        });
    }
}
