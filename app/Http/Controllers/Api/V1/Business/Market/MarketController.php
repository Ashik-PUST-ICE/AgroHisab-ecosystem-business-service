<?php

namespace App\Http\Controllers\Api\V1\Business\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Market\MarketRequest;
use App\Http\Resources\Business\Market\MarketResource;
use App\Models\Business\Market;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Market\MarketService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function __construct(protected MarketService $marketService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $markets = $this->marketService->index(
                $request->only(['search', 'type', 'status', 'district', 'per_page'])
            );

            return ApiResponse::success(MarketResource::collection($markets), 'Markets retrieved successfully');
        });
    }

    public function store(MarketRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $market = $this->marketService->store($request->validated());

            return ApiResponse::success(new MarketResource($market), 'Market created successfully', 201);
        });
    }

    public function show(Market $market)
    {
        return $this->handleRequest(function () use ($market) {
            $market = $this->marketService->show($market);

            return ApiResponse::success(new MarketResource($market), 'Market retrieved successfully');
        });
    }

    public function update(MarketRequest $request, Market $market)
    {
        return $this->handleRequest(function () use ($request, $market) {
            $market = $this->marketService->update($market, $request->validated());

            return ApiResponse::success(new MarketResource($market), 'Market updated successfully');
        });
    }

    public function destroy(Market $market)
    {
        return $this->handleRequest(function () use ($market) {
            $this->marketService->destroy($market);

            return ApiResponse::success(null, 'Market deleted successfully');
        });
    }
}