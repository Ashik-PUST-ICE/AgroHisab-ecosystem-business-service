<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Order\OrderResource;
use App\Http\Requests\Business\Order\OrderRequest;
use App\Models\Business\Order;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Order\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $orders = $this->orderService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($orders, 'Orders retrieved successfully');
        });
    }

    public function store(OrderRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $order = $this->orderService->store($request->validated());

            return ApiResponse::success(new OrderResource($order), 'Order created successfully', 201);
        });
    }

    public function show(Order $order)
    {
        return $this->handleRequest(function () use ($order) {
            $order = $this->orderService->show($order);

            return ApiResponse::success(new OrderResource($order), 'Order retrieved successfully');
        });
    }

    public function update(OrderRequest $request, Order $order)
    {
        return $this->handleRequest(function () use ($request, $order) {
            $order = $this->orderService->update($order, $request->validated());

            return ApiResponse::success(new OrderResource($order), 'Order updated successfully');
        });
    }

    public function destroy(Order $order)
    {
        return $this->handleRequest(function () use ($order) {
            $this->orderService->destroy($order);

            return ApiResponse::success(null, 'Order deleted successfully');
        });
    }
}
