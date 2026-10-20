<?php

namespace App\Http\Controllers\Api\V1\Business\Shipment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Shipment\DispatchRequest;
use App\Http\Requests\Business\Shipment\GoodsReceiptRequest;
use App\Http\Requests\Business\Shipment\ShipmentRequest;
use App\Http\Resources\Business\Shipment\ShipmentResource;
use App\Models\Business\Shipment;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Shipment\ShipmentService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(protected ShipmentService $shipmentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $shipments = $this->shipmentService->index(
                $request->only(['search', 'aratdar_id', 'transporter_id', 'status', 'from_date', 'to_date', 'per_page'])
            );

            return ApiResponse::success(ShipmentResource::collection($shipments), 'Shipments retrieved successfully');
        });
    }

    public function store(ShipmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $shipment = $this->shipmentService->store($request->validated());

            return ApiResponse::success(new ShipmentResource($shipment), 'Shipment created successfully', 201);
        });
    }

    public function show(Shipment $shipment)
    {
        return $this->handleRequest(function () use ($shipment) {
            $shipment = $this->shipmentService->show($shipment);

            return ApiResponse::success(new ShipmentResource($shipment), 'Shipment retrieved successfully');
        });
    }

    public function update(ShipmentRequest $request, Shipment $shipment)
    {
        return $this->handleRequest(function () use ($request, $shipment) {
            $shipment = $this->shipmentService->update($shipment, $request->validated());

            return ApiResponse::success(new ShipmentResource($shipment), 'Shipment updated successfully');
        });
    }

    public function destroy(Shipment $shipment)
    {
        return $this->handleRequest(function () use ($shipment) {
            $this->shipmentService->destroy($shipment);

            return ApiResponse::success(null, 'Shipment deleted successfully');
        });
    }

    public function dispatch(DispatchRequest $request, Shipment $shipment)
    {
        return $this->handleRequest(function () use ($request, $shipment) {
            $shipment = $this->shipmentService->dispatch($shipment, $request->validated());

            return ApiResponse::success(new ShipmentResource($shipment), 'Shipment dispatched successfully. Stock movements recorded.');
        });
    }

    public function receive(GoodsReceiptRequest $request, Shipment $shipment)
    {
        return $this->handleRequest(function () use ($request, $shipment) {
            $shipment = $this->shipmentService->receiveAtArat($shipment, $request->validated());

            return ApiResponse::success(new ShipmentResource($shipment), 'Goods received at arat. Stock movements recorded.');
        });
    }
}