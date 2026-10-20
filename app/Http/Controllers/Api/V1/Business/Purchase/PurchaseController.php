<?php

namespace App\Http\Controllers\Api\V1\Business\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Purchase\PurchaseRequest;
use App\Http\Requests\Business\Purchase\SupplierPaymentRequest;
use App\Http\Resources\Business\Purchase\PurchaseResource;
use App\Http\Resources\Business\Purchase\SupplierPaymentResource;
use App\Models\Business\Purchase;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Purchase\PurchaseService;
use App\Services\Business\Purchase\SupplierPaymentService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected SupplierPaymentService $supplierPaymentService
    ) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $purchases = $this->purchaseService->index(
                $request->only(['search', 'supplier_id', 'market_id', 'status', 'from_date', 'to_date', 'per_page'])
            );

            return ApiResponse::success(PurchaseResource::collection($purchases), 'Purchases retrieved successfully');
        });
    }

    public function store(PurchaseRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $purchase = $this->purchaseService->store($request->validated());

            return ApiResponse::success(new PurchaseResource($purchase), 'Purchase created successfully', 201);
        });
    }

    public function show(Purchase $purchase)
    {
        return $this->handleRequest(function () use ($purchase) {
            $purchase = $this->purchaseService->show($purchase);

            return ApiResponse::success(new PurchaseResource($purchase), 'Purchase retrieved successfully');
        });
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        return $this->handleRequest(function () use ($request, $purchase) {
            $purchase = $this->purchaseService->update($purchase, $request->validated());

            return ApiResponse::success(new PurchaseResource($purchase), 'Purchase updated successfully');
        });
    }

    public function destroy(Purchase $purchase)
    {
        return $this->handleRequest(function () use ($purchase) {
            $this->purchaseService->destroy($purchase);

            return ApiResponse::success(null, 'Purchase deleted successfully');
        });
    }

    public function receive(Request $request, Purchase $purchase)
    {
        return $this->handleRequest(function () use ($request, $purchase) {
            $purchase = $this->purchaseService->receive($purchase, $request->all());

            return ApiResponse::success(new PurchaseResource($purchase), 'Purchase stock received and movements recorded successfully');
        });
    }

    public function payments(Request $request, Purchase $purchase)
    {
        return $this->handleRequest(function () use ($purchase) {
            $payments = $this->supplierPaymentService->index($purchase);

            return ApiResponse::success(SupplierPaymentResource::collection($payments), 'Supplier payments retrieved successfully');
        });
    }

    public function storePayment(SupplierPaymentRequest $request, Purchase $purchase)
    {
        return $this->handleRequest(function () use ($request, $purchase) {
            $payment = $this->supplierPaymentService->store($purchase, $request->validated());

            return ApiResponse::success(new SupplierPaymentResource($payment), 'Supplier payment recorded successfully', 201);
        });
    }
}