<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\WorkDoneRequest;
use App\Http\Resources\Modules\Inventory\WorkDoneResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\WorkDoneService;
use Illuminate\Http\Request;

class WorkDoneController extends Controller
{
    public function __construct(protected WorkDoneService $workDoneService) {}

    /**
     * Get all work dones with pagination
     */
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->workDoneService->index($request->all());

            return ApiResponse::success(WorkDoneResource::collection($data), 'Work dones fetched successfully');
        });
    }

    /**
     * Create or update work done
     */
    public function store(WorkDoneRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->workDoneService->store($request->validated());

            return ApiResponse::success(WorkDoneResource::make($data), 'Work done saved successfully');
        });
    }
}
