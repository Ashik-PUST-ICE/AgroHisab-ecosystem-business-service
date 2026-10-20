<?php

namespace App\Http\Controllers\Api\V1\Business\Shipment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Shipment\DispatchRequest;
use App\Http\Requests\Business\Shipment\GoodsReceiptRequest;
use App\Http\Requests\Business\Shipment\ShipmentRequest;
use App\Http\Requests\Business\Shipment\ShipmentSaleRequest;
use App\Http\Resources\Business\Shipment\ShipmentResource;
use App\Http\Resources\Business\Shipment\ShipmentSaleResource;
use App\Models\Business\Shipment;
use App\Services\Business\Shipment\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'aratdar_id', 'transporter_id', 'search', 'per_page']);
        $shipments = $this->shipmentService->list($filters);

        return response()->json([
            'status'  => true,
            'message' => getMessage(DATA_FETCH_SUCCESSFULLY),
            'data'    => ShipmentResource::collection($shipments)->response()->getData(true),
        ]);
    }

    public function store(ShipmentRequest $request): JsonResponse
    {
        $shipment = $this->shipmentService->create($request->validated());

        return response()->json([
            'status'  => true,
            'message' => getMessage(CREATED_SUCCESSFULLY),
            'data'    => new ShipmentResource($shipment),
        ], 201);
    }

    public function show(Shipment $shipment): JsonResponse
    {
        $shipment = $this->shipmentService->show($shipment);

        return response()->json([
            'status'  => true,
            'message' => getMessage(DATA_FETCH_SUCCESSFULLY),
            'data'    => new ShipmentResource($shipment),
        ]);
    }

    public function update(ShipmentRequest $request, Shipment $shipment): JsonResponse
    {
        $shipment = $this->shipmentService->update($shipment, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => getMessage(UPDATED_SUCCESSFULLY),
            'data'    => new ShipmentResource($shipment),
        ]);
    }

    public function destroy(Shipment $shipment): JsonResponse
    {
        $this->shipmentService->destroy($shipment);

        return response()->json([
            'status'  => true,
            'message' => getMessage(DELETED_SUCCESSFULLY),
        ]);
    }

    public function dispatch(DispatchRequest $request, Shipment $shipment): JsonResponse
    {
        $shipment = $this->shipmentService->dispatch($shipment, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Shipment dispatched successfully',
            'data'    => new ShipmentResource($shipment),
        ]);
    }

    public function receive(GoodsReceiptRequest $request, Shipment $shipment): JsonResponse
    {
        $shipment = $this->shipmentService->receiveAtArat($shipment, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Goods receipt at arat recorded successfully',
            'data'    => new ShipmentResource($shipment),
        ]);
    }

    public function challan(Request $request, Shipment $shipment): JsonResponse|Response
    {
        if ($request->query('format') === 'html' || $request->is('*challan-pdf')) {
            $html = $this->shipmentService->renderChallanHtml($shipment);
            return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $challanData = $this->shipmentService->getChallan($shipment);

        return response()->json([
            'status'  => true,
            'message' => getMessage(DATA_FETCH_SUCCESSFULLY),
            'data'    => $challanData,
        ]);
    }

    public function sales(Shipment $shipment): JsonResponse
    {
        $sales = $this->shipmentService->getSales($shipment);

        return response()->json([
            'status'  => true,
            'message' => getMessage(DATA_FETCH_SUCCESSFULLY),
            'data'    => ShipmentSaleResource::collection($sales),
        ]);
    }

    public function storeSale(ShipmentSaleRequest $request, Shipment $shipment): JsonResponse
    {
        $sale = $this->shipmentService->recordSale($shipment, $request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Sale recorded successfully',
            'data'    => new ShipmentSaleResource($sale),
        ], 201);
    }

    public function stock(Shipment $shipment): JsonResponse
    {
        $stockData = $this->shipmentService->getStockBalance($shipment);

        return response()->json([
            'status'  => true,
            'message' => getMessage(DATA_FETCH_SUCCESSFULLY),
            'data'    => $stockData,
        ]);
    }
}