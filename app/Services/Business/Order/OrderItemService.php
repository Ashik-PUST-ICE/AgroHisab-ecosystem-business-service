<?php

namespace App\Services\Business\Order;

use App\Models\Business\Order;
use App\Models\Business\OrderItem;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderItemService
{
    public function index(Order $order, array $filters): LengthAwarePaginator
    {
        return $order->items()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%")))
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(Order $order, array $data): OrderItem
    {
        return $order->items()->create($data);
    }

    public function show(Order $order, OrderItem $orderItem): OrderItem
    {
        return $orderItem->load('product');
    }

    public function update(Order $order, OrderItem $orderItem, array $data): OrderItem
    {
        $orderItem->update($data);

        return $orderItem;
    }

    public function destroy(Order $order, OrderItem $orderItem): void
    {
        $orderItem->delete();
    }
}
