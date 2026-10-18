<?php

namespace App\Services\Business\Order;

use App\Models\Business\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Order::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Order
    {
        return Order::create($data);
    }

    public function show(Order $order): Order
    {
        return $order->load('items.product');
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function destroy(Order $order): void
    {
        $order->delete();
    }
}
