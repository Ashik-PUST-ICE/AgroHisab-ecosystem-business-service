<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\TjBoxRequest;
use App\Http\Resources\Modules\Inventory\TjBoxResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\TjBoxService;
use Illuminate\Http\Request;

class TjBoxController extends Controller
{
    public function __construct(protected TjBoxService $tjBoxService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->tjBoxService->index($request->all());

            return ApiResponse::success(($data), 'Data fetched successfully');
        });
    }

    public function store(TjBoxRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->tjBoxService->store($request->validated());

            return ApiResponse::success(TjBoxResource::make($data), 'TJ Box created successfully', 201);
        });
    }

    public function getAvailableList(Request $request)
    {
        return $this->handleRequest(function () {
            $data = $this->tjBoxService->getAvailableList();

            return ApiResponse::success(($data), 'List fetched successfully');
        });
    }
}
