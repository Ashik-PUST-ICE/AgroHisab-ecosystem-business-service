<?php

namespace App\Http\Controllers\Api\V1\Business\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Market\PriceObservationRequest;
use App\Http\Resources\Business\Market\PriceObservationResource;
use App\Models\Business\Market;
use App\Models\Business\PriceObservation;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Market\PriceObservationService;
use Illuminate\Http\Request;

class PriceObservationController extends Controller
{
    public function __construct(protected PriceObservationService $priceService) {}

    /**
     * GET /api/v1/markets/{market}/price-observations
     * Spec §3.4: Daily price observations by product, unit, date, and source.
     * Query: product_id, from_date, to_date, per_page
     */
    public function index(Request $request, Market $market)
    {
        return $this->handleRequest(function () use ($request, $market) {
            $obs = $this->priceService->list(
                $market,
                $request->only(['product_id', 'from_date', 'to_date', 'per_page'])
            );
            return ApiResponse::success(PriceObservationResource::collection($obs), 'Price observations retrieved successfully');
        });
    }

    /**
     * POST /api/v1/markets/{market}/price-observations
     */
    public function store(PriceObservationRequest $request, Market $market)
    {
        return $this->handleRequest(function () use ($request, $market) {
            $obs = $this->priceService->store($market, $request->validated());
            $obs->load(['product', 'unit']);
            return ApiResponse::success(new PriceObservationResource($obs), 'Price observation recorded successfully', 201);
        });
    }

    /**
     * DELETE /api/v1/price-observations/{observation}
     */
    public function destroy(PriceObservation $priceObservation)
    {
        return $this->handleRequest(function () use ($priceObservation) {
            $this->priceService->destroy($priceObservation);
            return ApiResponse::success(null, 'Price observation deleted successfully');
        });
    }
}