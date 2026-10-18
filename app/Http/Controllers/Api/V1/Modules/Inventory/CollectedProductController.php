<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\CollectedProductRequest;
use App\Http\Resources\Modules\Inventory\CollectedProductResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\CollectedProductService;
use Illuminate\Http\Request;

class CollectedProductController extends Controller
{
    public function __construct(protected CollectedProductService $collectedProductService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->collectedProductService->index($request->all());

            return ApiResponse::success(CollectedProductResource::collection($data->load(['stockCategory', 'stockProduct', 'vendor', 'unit', 'collectable'])), 'Data fetched successfully');
        });
    }

    public function store(CollectedProductRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->collectedProductService->store($request->validated());

            return ApiResponse::success(CollectedProductResource::make($data->load(['stockCategory', 'stockProduct', 'vendor', 'unit', 'collectable'])), 'Collected product created successfully', 201);
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->collectedProductService->show($id, ['stockCategory', 'stockProduct', 'vendor', 'unit', 'collectable']);

            return ApiResponse::success(CollectedProductResource::make($data));
        });
    }
}
