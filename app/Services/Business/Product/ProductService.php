<?php

namespace App\Services\Business\Product;

use App\Models\Business\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Product
    {
        return Product::create($data);
    }

    public function show(Product $product): Product
    {
        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function destroy(Product $product): void
    {
        $product->delete();
    }
}
