<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\PatchCardRequest;
use App\Http\Resources\Modules\Inventory\PatchCardResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\PatchCardService;
use Illuminate\Http\Request;

class PatchCardController extends Controller
{
    public function __construct(protected PatchCardService $patchCardService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->patchCardService->index($request->all());

            return ApiResponse::success(($data), 'Data fetched successfully');
        });
    }

    public function store(PatchCardRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->patchCardService->store($request->validated());

            return ApiResponse::success(PatchCardResource::make($data), 'Patch Card created successfully', 201);
        });
    }

    public function getAvailableList(Request $request)
    {
        return $this->handleRequest(function () {
            $data = $this->patchCardService->getAvailableList();

            return ApiResponse::success(($data), 'List fetched successfully');
        });
    }
}
