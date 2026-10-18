<?php

namespace App\Http\Controllers\Api\V1\Modules\Hrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Hrm\ShiftRequest;
use App\Http\Resources\Modules\Hrm\ShiftResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Hrm\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->shiftService->index($request->all());

            return ApiResponse::success(ShiftResource::collection($data));
        });
    }

    public function store(ShiftRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->shiftService->store($request->validated());

            return ApiResponse::success(ShiftResource::make($data), 'Shift created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->shiftService->show($id, ['workSchedule']);

            return ApiResponse::success(ShiftResource::make($data));
        });
    }

    public function update(ShiftRequest $request, string $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $this->shiftService->update($id, $request->validated());

            return ApiResponse::success(ShiftResource::make($data), 'Shift updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->shiftService->destroy($id);

            return ApiResponse::success([], 'Shift deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->shiftService->status($id);

            return ApiResponse::success(ShiftResource::make($data), 'Status toggled successfully');
        });
    }

    public function list()
    {
        return $this->handleRequest(function () {
            $data = $this->shiftService->list();

            return ApiResponse::success($data);
        });
    }
}
