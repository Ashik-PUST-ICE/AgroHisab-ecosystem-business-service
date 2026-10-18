<?php

namespace App\Http\Controllers\Api\V1\Business\BeatOutlet;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Beat\BeatOutletResource;
use App\Models\Business\Beat;
use App\Models\Business\BeatOutlet;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Beat\BeatOutletService;
use Illuminate\Http\Request;

class BeatOutletController extends Controller
{
    public function __construct(protected BeatOutletService $beatOutletService) {}

    public function index(Beat $beat)
    {
        return $this->handleRequest(function () use ($beat) {
            $outlets = $this->beatOutletService->index($beat);

            return ApiResponse::success(BeatOutletResource::collection($outlets), 'Beat outlets retrieved successfully');
        });
    }

    public function store(Request $request, Beat $beat)
    {
        return $this->handleRequest(function () use ($request, $beat) {
            $request->validate([
                'outlet_id' => ['required', 'exists:outlets,id'],
                'sequence' => ['nullable', 'integer', 'min:1'],
                'status' => ['nullable', 'boolean'],
            ]);

            $beatOutlet = $this->beatOutletService->store($beat, $request->only(['outlet_id', 'sequence', 'status']));

            return ApiResponse::success(new BeatOutletResource($beatOutlet->load('outlet')), 'Outlet added to beat successfully', 201);
        });
    }

    public function destroy(Beat $beat, BeatOutlet $beatOutlet)
    {
        return $this->handleRequest(function () use ($beat, $beatOutlet) {
            $this->beatOutletService->destroy($beat, $beatOutlet);

            return ApiResponse::success(null, 'Outlet removed from beat successfully');
        });
    }
}
