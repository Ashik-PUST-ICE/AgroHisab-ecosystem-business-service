<?php

namespace App\Http\Controllers\Api\V1\Business\OrderItem;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Order\OrderItemResource;
use App\Http\Requests\Business\Order\OrderItemRequest;
use App\Models\Business\Order;
use App\Models\Business\OrderItem;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Order\OrderItemService;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function __construct(protected OrderItemService $orderItemService) {}

    public function index(Request $request, Order $order)
    {
        return $this->handleRequest(function () use ($request, $order) {
            $items = $this->orderItemService->index($order, $request->only(['search', 'per_page']));

            return ApiResponse::success($items, 'Order items retrieved successfully');
        });
    }

    public function store(OrderItemRequest $request, Order $order)
    {
        return $this->handleRequest(function () use ($request, $order) {
            $item = $this->orderItemService->store($order, $request->validated());

            return ApiResponse::success(new OrderItemResource($item), 'Order item created successfully', 201);
        });
    }

    public function show(Request $request, Order $order, OrderItem $orderItem)
    {
        return $this->handleRequest(function () use ($order, $orderItem) {
            $item = $this->orderItemService->show($order, $orderItem);

            return ApiResponse::success(new OrderItemResource($item), 'Order item retrieved successfully');
        });
    }

    public function update(OrderItemRequest $request, Order $order, OrderItem $orderItem)
    {
        return $this->handleRequest(function () use ($request, $order, $orderItem) {
            $item = $this->orderItemService->update($order, $orderItem, $request->validated());

            return ApiResponse::success(new OrderItemResource($item), 'Order item updated successfully');
        });
    }

    public function destroy(Order $order, OrderItem $orderItem)
    {
        return $this->handleRequest(function () use ($order, $orderItem) {
            $this->orderItemService->destroy($order, $orderItem);

            return ApiResponse::success(null, 'Order item deleted successfully');
        });
    }
}
