<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory\Invoice;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\Invoice\InvoiceService;
use App\Http\Requests\Modules\Inventory\Invoice\InvoiceRequest;
use App\Http\Resources\Modules\Inventory\Invoice\InvoiceResource;


class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->invoiceService->index($request->all());

            return ApiResponse::success(InvoiceResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(InvoiceRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->invoiceService->store($request->validated());

            return ApiResponse::success(InvoiceResource::make($data), 'Invoice Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->invoiceService->show($id);

            return ApiResponse::success(InvoiceResource::make($data));
        });
    }

    public function update(string $id, InvoiceRequest $request)
    {
        return $this->handleRequest(function () use ($id, $request) {
            $data = $this->invoiceService->update($id, $request->validated());

            return ApiResponse::success(InvoiceResource::make($data), 'Invoice updated successfully');
        });
    }

    public function destroy(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->invoiceService->destroy($id);

            return ApiResponse::success($data, 'Invoice Deleted successfully');
        });
    }

    public function status(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->invoiceService->toggleStatus($id);

            return ApiResponse::success(InvoiceResource::make($data), 'Status toggled successfully');
        });
    }

    public function list(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->invoiceService->list($request->all());

            return ApiResponse::success($data, 'Data fetched successfully');
        });
    }


    public function invoiceDetails(Request $request, string $id)
    {
        return $this->handleRequest(function () use ($id) {

            $data = $this->invoiceService->invoiceDetails($id);
            return ApiResponse::success($data, 'Data Get successfully');
        });
    }


    public function getRecurringInvoices()
    {
        return $this->handleRequest(function () {
            $data = $this->invoiceService->getRecurringInvoices();

            return ApiResponse::success($data, 'Recurring invoices retrieved successfully');
        });
    }
}
