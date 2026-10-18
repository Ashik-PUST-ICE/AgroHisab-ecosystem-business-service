<?php

namespace App\Services\Business\Product;

use App\Models\Business\ProductCategory;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCategoryService
{
    public function index(array $filters): LengthAwarePaginator
    {
        return ProductCategory::query()
            ->when($filters['search'] ?? null, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): ProductCategory
    {
        return ProductCategory::create($data);
    }

    public function show(ProductCategory $category): ProductCategory
    {
        return $category;
    }

    public function update(ProductCategory $category, array $data): ProductCategory
    {
        $category->update($data);

        return $category;
    }

    public function destroy(ProductCategory $category): void
    {
        $category->delete();
    }
}
