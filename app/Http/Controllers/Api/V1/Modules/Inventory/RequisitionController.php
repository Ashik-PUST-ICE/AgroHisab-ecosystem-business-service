<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\RequisitionService;
use App\Http\Requests\Modules\Inventory\RequisitionRequest;
use App\Http\Resources\Modules\Inventory\Requisitions\RequisitionResource;
use App\Http\Resources\Modules\Inventory\Requisitions\StockHistoryResource;
use App\Http\Resources\Modules\Inventory\Requisitions\RequisitionListResource;
use App\Http\Resources\Modules\Inventory\Requisitions\RequisitionDetailsResource;

class RequisitionController extends Controller
{
    public function __construct(protected RequisitionService $requisitionService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->requisitionService->index($request->all());

            return ApiResponse::success(RequisitionListResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(RequisitionRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->requisitionService->store($request->validated());

            return ApiResponse::success(RequisitionResource::make($data), 'Requisition Created successfully');
        });
    }

    public function show(Request $request, string $id)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $relations = match ($request->input('type')) {
                'details' => ['brand', 'stockCategory', 'product', 'vendor', 'brand', 'unit'],
                'purchase' => ['vendor', 'brand', 'unit', 'product:id,name,stock_category_id,is_serial_mandatory'],
                default => [],
            };
            $data = $this->requisitionService->show($id, $relations);

            $resource = match ($request->input('type')) {
                'details' => RequisitionDetailsResource::make($data),
                'purchase' => RequisitionDetailsResource::make($data),
                default => RequisitionResource::make($data),
            };

            return ApiResponse::success($resource, 'Data fetched successfully');
        });
    }

    public function update(string $id, RequisitionRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->requisitionService->update($id, $request->validated());

            return ApiResponse::success(RequisitionResource::make($data), 'Requisition Updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->requisitionService->destroy($id);

            return ApiResponse::success($data, 'Requisition Deleted successfully');
        });
    }

    public function approve(string $id, Request $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $request->validate([
                'approval_type' => 'required|in:approve,cancel',
                'comment' => 'required|string|max:1000',
            ]);

            $approvalType = $request->input('approval_type');

            $data = $this->requisitionService->approve($id, $approvalType, $request->comment);

            $message = match ($approvalType) {
                'approve' => 'Requisition approved successfully',
                'cancel' => 'Requisition cancelled successfully',
            };

            return ApiResponse::success(RequisitionResource::make($data), $message);
        });
    }





}
