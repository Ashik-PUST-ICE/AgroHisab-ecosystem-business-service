<?php

namespace Tests\Feature\Business;

use App\Models\Business\Product;
use App\Models\Business\ProductCategory;
use App\Models\Business\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithJwtAuth;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithJwtAuth;

    public function test_can_create_and_manage_product_categories(): void
    {
        $this->authenticateJwt(userId: 1);

        $response = $this->postJson('/api/v1/product-categories', [
            'name' => 'পাট (Jute)',
            'description' => 'Tosha and Deshi Jute produce',
            'status' => STATUS_ACTIVE,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'পাট (Jute)')
            ->assertJsonPath('data.status', STATUS_ACTIVE);

        $this->assertDatabaseHas('product_categories', [
            'name' => 'পাট (Jute)',
            'status' => STATUS_ACTIVE,
        ]);

        $list = $this->getJson('/api/v1/product-categories');
        $list->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_and_filter_products(): void
    {
        $this->authenticateJwt(userId: 1);

        $category = ProductCategory::create(['name' => 'পেঁয়াজ (Onion)', 'status' => STATUS_ACTIVE]);
        $mon = Unit::create(['name' => 'Maund / মন', 'code' => 'mon', 'status' => STATUS_ACTIVE]);

        $response = $this->postJson('/api/v1/products', [
            'category_id' => $category->id,
            'default_unit_id' => $mon->id,
            'name' => 'তাহেরপুরী পেঁয়াজ',
            'variety' => 'Grade A / সুপার কোয়ালিটি',
            'description' => 'Locally sourced from Kumarkhali',
            'status' => STATUS_ACTIVE,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'তাহেরপুরী পেঁয়াজ')
            ->assertJsonPath('data.variety', 'Grade A / সুপার কোয়ালিটি')
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.default_unit_id', $mon->id)
            ->assertJsonPath('data.status', STATUS_ACTIVE);

        $this->assertDatabaseHas('products', [
            'name' => 'তাহেরপুরী পেঁয়াজ',
            'category_id' => $category->id,
            'default_unit_id' => $mon->id,
            'status' => STATUS_ACTIVE,
        ]);

        // Filter by category_id
        $filtered = $this->getJson('/api/v1/products?category_id=' . $category->id);
        $filtered->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}