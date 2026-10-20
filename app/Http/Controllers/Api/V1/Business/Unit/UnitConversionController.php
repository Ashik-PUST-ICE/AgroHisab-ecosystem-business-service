<?php

namespace App\Http\Controllers\Api\V1\Business\Unit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Product\UnitConversionRequest;
use App\Http\Resources\Business\Product\UnitConversionResource;
use App\Models\Business\UnitConversion;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Product\UnitConversionService;
use Illuminate\Http\Request;

class UnitConversionController extends Controller
{
    public function __construct(protected UnitConversionService $conversionService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $conversions = $this->conversionService->index($request->only(['from_unit_id', 'to_unit_id', 'per_page']));

            return ApiResponse::success(UnitConversionResource::collection($conversions), 'Unit conversions retrieved successfully');
        });
    }

    public function store(UnitConversionRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $conversion = $this->conversionService->store($request->validated());

            return ApiResponse::success(new UnitConversionResource($conversion), 'Unit conversion created successfully', 201);
        });
    }

    public function show(UnitConversion $conversion)
    {
        return $this->handleRequest(function () use ($conversion) {
            $conversion = $this->conversionService->show($conversion);

            return ApiResponse::success(new UnitConversionResource($conversion), 'Unit conversion retrieved successfully');
        });
    }

    public function update(UnitConversionRequest $request, UnitConversion $conversion)
    {
        return $this->handleRequest(function () use ($request, $conversion) {
            $conversion = $this->conversionService->update($conversion, $request->validated());

            return ApiResponse::success(new UnitConversionResource($conversion), 'Unit conversion updated successfully');
        });
    }

    public function destroy(UnitConversion $conversion)
    {
        return $this->handleRequest(function () use ($conversion) {
            $this->conversionService->destroy($conversion);

            return ApiResponse::success(null, 'Unit conversion deleted successfully');
        });
    }

    public function convert(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'from_unit_id' => 'required|integer',
                'to_unit_id' => 'required|integer',
                'quantity' => 'required|numeric|min:0',
            ]);

            $result = $this->conversionService->convert(
                (int) $request->input('from_unit_id'),
                (int) $request->input('to_unit_id'),
                (float) $request->input('quantity')
            );

            if ($result === null) {
                return ApiResponse::error(new \Exception('Conversion rate not found between specified units', 404));
            }

            return ApiResponse::success([
                'from_unit_id' => (int) $request->input('from_unit_id'),
                'to_unit_id' => (int) $request->input('to_unit_id'),
                'original_quantity' => (float) $request->input('quantity'),
                'converted_quantity' => $result,
            ], 'Quantity converted successfully');
        });
    }
}
