<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory\Invoice;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Http\Resources\Modules\Inventory\Invoice\InvoicePaymentResource;
use App\Http\Requests\Modules\Inventory\Invoice\InvoicePaymentRequest;
use App\Services\Modules\Inventory\Invoice\InvoicePaymentService;

class InvoicePaymentController extends Controller
{
    public function __construct(protected InvoicePaymentService $invoicePaymentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->invoicePaymentService->index($request->all());

            return ApiResponse::success(InvoicePaymentResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(InvoicePaymentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->invoicePaymentService->store($request->validated());

            return ApiResponse::success(InvoicePaymentResource::make($data), 'Payment Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->invoicePaymentService->show($id, ['invoice', 'account', 'transaction']);

            return ApiResponse::success(InvoicePaymentResource::make($data), 'Payment retrieved successfully');
        });
    }





}
