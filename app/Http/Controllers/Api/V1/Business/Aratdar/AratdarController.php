<?php

namespace App\Http\Controllers\Api\V1\Business\Aratdar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Aratdar\AratdarRequest;
use App\Http\Resources\Business\Aratdar\AratdarResource;
use App\Models\Business\Aratdar;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Aratdar\AratdarService;
use Illuminate\Http\Request;

class AratdarController extends Controller
{
    public function __construct(protected AratdarService $aratdarService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $aratdars = $this->aratdarService->index(
                $request->only(['search', 'status', 'district', 'commission_type', 'per_page'])
            );

            return ApiResponse::success(AratdarResource::collection($aratdars), 'Aratdars retrieved successfully');
        });
    }

    public function store(AratdarRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $aratdar = $this->aratdarService->store($request->validated());

            return ApiResponse::success(new AratdarResource($aratdar), 'Aratdar created successfully', 201);
        });
    }

    public function show(Aratdar $aratdar)
    {
        return $this->handleRequest(function () use ($aratdar) {
            $aratdar = $this->aratdarService->show($aratdar);

            return ApiResponse::success(new AratdarResource($aratdar), 'Aratdar retrieved successfully');
        });
    }

    public function update(AratdarRequest $request, Aratdar $aratdar)
    {
        return $this->handleRequest(function () use ($request, $aratdar) {
            $aratdar = $this->aratdarService->update($aratdar, $request->validated());

            return ApiResponse::success(new AratdarResource($aratdar), 'Aratdar updated successfully');
        });
    }

    public function destroy(Aratdar $aratdar)
    {
        return $this->handleRequest(function () use ($aratdar) {
            $this->aratdarService->destroy($aratdar);

            return ApiResponse::success(null, 'Aratdar deleted successfully');
        });
    }
}
