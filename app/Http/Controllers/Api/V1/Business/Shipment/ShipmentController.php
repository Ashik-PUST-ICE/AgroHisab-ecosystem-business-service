<?php

namespace App\Http\Controllers\Api\V1\Business\Shipment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Shipment\DispatchRequest;
use App\Http\Requests\Business\Shipment\GoodsReceiptRequest;
use App\Http\Requests\Business\Shipment\ShipmentRequest;
use App\Http\Requests\Business\Shipment\ShipmentSaleRequest;
use App\Http\Resources\Business\Shipment\SettlementResource;
use App\Http\Resources\Business\Shipment\ShipmentResource;
use App\Http\Resources\Business\Shipment\ShipmentSaleResource;
use App\Models\Business\Shipment;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Shipment\ShipmentService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $shipments = $this->shipmentService->list(
                $request->only(['status', 'aratdar_id', 'transporter_id', 'from_date', 'to_date', 'search', 'per_page'])
            );

            return ApiResponse::success(ShipmentResource::collection($shipments), 'Shipments retrieved successfully');
        });
    }

    public function store(ShipmentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $shipment = $this->shipmentService->create($request->validated());

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

            return ApiResponse::success(new ShipmentResource($shipment), 'Shipment dispatched successfully');
        });
    }

    public function receive(GoodsReceiptRequest $request, Shipment $shipment)
    {
        return $this->handleRequest(function () use ($request, $shipment) {
            $shipment = $this->shipmentService->receiveAtArat($shipment, $request->validated());

            return ApiResponse::success(new ShipmentResource($shipment), 'Goods receipt at arat recorded successfully');
        });
    }

    public function challan(Request $request, Shipment $shipment)
    {
        if ($request->query('format') === 'html' || $request->is('*challan-pdf')) {
            $html = $this->shipmentService->renderChallanHtml($shipment);
            return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return $this->handleRequest(function () use ($shipment) {
            $challanData = $this->shipmentService->getChallan($shipment);

            return ApiResponse::success($challanData, 'Delivery challan retrieved successfully');
        });
    }

    public function sales(Shipment $shipment)
    {
        return $this->handleRequest(function () use ($shipment) {
            $sales = $this->shipmentService->getSales($shipment);

            return ApiResponse::success(ShipmentSaleResource::collection($sales), 'Shipment sales retrieved successfully');
        });
    }

    public function storeSale(ShipmentSaleRequest $request, Shipment $shipment)
    {
        return $this->handleRequest(function () use ($request, $shipment) {
            $sale = $this->shipmentService->recordSale($shipment, $request->validated());

            return ApiResponse::success(new ShipmentSaleResource($sale), 'Sale recorded successfully', 201);
        });
    }

    public function stock(Shipment $shipment)
    {
        return $this->handleRequest(function () use ($shipment) {
            $stockData = $this->shipmentService->getStockBalance($shipment);

            return ApiResponse::success($stockData, 'Shipment stock balance retrieved successfully');
        });
    }

    public function settlementPreview(Shipment $shipment)
    {
        return $this->handleRequest(function () use ($shipment) {
            $preview = $this->shipmentService->getSettlementPreview($shipment);

            return ApiResponse::success($preview, 'Consignment settlement preview calculated successfully');
        });
    }

    public function settle(Request $request, Shipment $shipment)
    {
        return $this->handleRequest(function () use ($request, $shipment) {
            $settlement = $this->shipmentService->settle($shipment, $request->all());

            return ApiResponse::success(new SettlementResource($settlement), 'Shipment settlement locked and approved successfully', 201);
        });
    }

    public function settlement(Shipment $shipment)
    {
        return $this->handleRequest(function () use ($shipment) {
            $settlement = $this->shipmentService->getSettlement($shipment);

            return ApiResponse::success(
                $settlement ? new SettlementResource($settlement) : null,
                $settlement ? 'Settlement retrieved successfully' : 'No settlement found for this shipment'
            );
        });
    }
}