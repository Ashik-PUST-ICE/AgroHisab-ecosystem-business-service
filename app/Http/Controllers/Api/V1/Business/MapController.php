<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Outlet\OutletResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Map\MapService;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(protected MapService $mapService) {}

    public function outlets(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $outlets = $this->mapService->outlets($request->only(['lat', 'lng', 'radius', 'beat_id', 'status']));

            return ApiResponse::success(OutletResource::collection($outlets), 'Map outlets retrieved successfully');
        });
    }

    public function nearby(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
                'radius' => ['nullable', 'integer', 'min:100', 'max:100000'],
            ]);

            $outlets = $this->mapService->nearby($request->only(['lat', 'lng', 'radius']));

            return ApiResponse::success(OutletResource::collection($outlets), 'Nearby outlets retrieved successfully');
        });
    }
}
