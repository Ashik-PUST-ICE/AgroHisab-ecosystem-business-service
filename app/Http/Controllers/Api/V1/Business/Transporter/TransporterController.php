<?php

namespace App\Http\Controllers\Api\V1\Business\Transporter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Transporter\TransporterRequest;
use App\Http\Resources\Business\Transporter\TransporterResource;
use App\Models\Business\Transporter;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Transporter\TransporterService;
use Illuminate\Http\Request;

class TransporterController extends Controller
{
    public function __construct(protected TransporterService $transporterService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $transporters = $this->transporterService->index(
                $request->only(['search', 'status', 'per_page'])
            );

            return ApiResponse::success(TransporterResource::collection($transporters), 'Transporters retrieved successfully');
        });
    }

    public function store(TransporterRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $transporter = $this->transporterService->store($request->validated());

            return ApiResponse::success(new TransporterResource($transporter), 'Transporter created successfully', 201);
        });
    }

    public function show(Transporter $transporter)
    {
        return $this->handleRequest(function () use ($transporter) {
            $transporter = $this->transporterService->show($transporter);

            return ApiResponse::success(new TransporterResource($transporter), 'Transporter retrieved successfully');
        });
    }

    public function update(TransporterRequest $request, Transporter $transporter)
    {
        return $this->handleRequest(function () use ($request, $transporter) {
            $transporter = $this->transporterService->update($transporter, $request->validated());

            return ApiResponse::success(new TransporterResource($transporter), 'Transporter updated successfully');
        });
    }

    public function destroy(Transporter $transporter)
    {
        return $this->handleRequest(function () use ($transporter) {
            $this->transporterService->destroy($transporter);

            return ApiResponse::success(null, 'Transporter deleted successfully');
        });
    }
}