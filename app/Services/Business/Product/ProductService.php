<?php

namespace App\Services\Business\Product;

use App\Models\Business\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'defaultUnit'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('variety', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['category_id']), fn($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): Product
    {
        if (function_exists('authId') && authId()) {
            $data['created_by'] = authId();
        }
        $product = Product::create($data);
        return $product->load(['category', 'defaultUnit']);
    }

    public function show(Product $product): Product
    {
        return $product->load(['category', 'defaultUnit']);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->load(['category', 'defaultUnit']);
    }

    public function destroy(Product $product): void
    {
        $product->delete();
    }
}