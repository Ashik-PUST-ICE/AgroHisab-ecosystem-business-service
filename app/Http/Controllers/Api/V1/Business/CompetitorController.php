<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Competitor\CompetitorResource;
use App\Http\Requests\Business\Competitor\CompetitorRequest;
use App\Models\Business\Competitor;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Competitor\CompetitorService;
use Illuminate\Http\Request;

class CompetitorController extends Controller
{
    public function __construct(protected CompetitorService $competitorService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $competitors = $this->competitorService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($competitors, 'Competitors retrieved successfully');
        });
    }

    public function store(CompetitorRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $competitor = $this->competitorService->store($request->validated());

            return ApiResponse::success(new CompetitorResource($competitor), 'Competitor created successfully', 201);
        });
    }

    public function show(Competitor $competitor)
    {
        return $this->handleRequest(function () use ($competitor) {
            $competitor = $this->competitorService->show($competitor);

            return ApiResponse::success(new CompetitorResource($competitor), 'Competitor retrieved successfully');
        });
    }

    public function update(CompetitorRequest $request, Competitor $competitor)
    {
        return $this->handleRequest(function () use ($request, $competitor) {
            $competitor = $this->competitorService->update($competitor, $request->validated());

            return ApiResponse::success(new CompetitorResource($competitor), 'Competitor updated successfully');
        });
    }

    public function destroy(Competitor $competitor)
    {
        return $this->handleRequest(function () use ($competitor) {
            $this->competitorService->destroy($competitor);

            return ApiResponse::success(null, 'Competitor deleted successfully');
        });
    }
}
