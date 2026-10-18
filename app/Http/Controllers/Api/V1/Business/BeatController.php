<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Beat\BeatResource;
use App\Http\Requests\Business\Beat\BeatRequest;
use App\Models\Business\Beat;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Beat\BeatService;
use Illuminate\Http\Request;

class BeatController extends Controller
{
    public function __construct(protected BeatService $beatService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $beats = $this->beatService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($beats, 'Beats retrieved successfully');
        });
    }

    public function today(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $beats = $this->beatService->today($request->only(['per_page']));

            return ApiResponse::success($beats, 'Today beats retrieved successfully');
        });
    }

    public function store(BeatRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $beat = $this->beatService->store($request->validated());

            return ApiResponse::success(new BeatResource($beat), 'Beat created successfully', 201);
        });
    }

    public function show(Beat $beat)
    {
        return $this->handleRequest(function () use ($beat) {
            $beat = $this->beatService->show($beat);

            return ApiResponse::success(new BeatResource($beat), 'Beat retrieved successfully');
        });
    }

    public function update(BeatRequest $request, Beat $beat)
    {
        return $this->handleRequest(function () use ($request, $beat) {
            $beat = $this->beatService->update($beat, $request->validated());

            return ApiResponse::success(new BeatResource($beat), 'Beat updated successfully');
        });
    }

    public function destroy(Beat $beat)
    {
        return $this->handleRequest(function () use ($beat) {
            $this->beatService->destroy($beat);

            return ApiResponse::success(null, 'Beat deleted successfully');
        });
    }
}
