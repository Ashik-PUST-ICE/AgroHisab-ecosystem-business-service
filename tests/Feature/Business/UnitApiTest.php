<?php

namespace Tests\Feature\Business;

use App\Models\Business\Unit;
use App\Models\Business\UnitConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithJwtAuth;

class UnitApiTest extends TestCase
{
    use RefreshDatabase, WithJwtAuth;

    public function test_can_create_and_list_units(): void
    {
        $this->authenticateJwt(userId: 1);

        $response = $this->postJson('/api/v1/units', [
            'name' => 'Kilogram',
            'code' => 'kg',
            'is_base_unit' => true,
            'status' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Kilogram')
            ->assertJsonPath('data.code', 'kg')
            ->assertJsonPath('data.is_base_unit', true)
            ->assertJsonPath('data.status', 1);

        $this->assertDatabaseHas('units', [
            'name' => 'Kilogram',
            'status' => 1,
        ]);

        $listResponse = $this->getJson('/api/v1/units');
        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_conversion_and_convert_quantities(): void
    {
        $this->authenticateJwt(userId: 1);

        $mon = Unit::create(['name' => 'Maund / মন', 'code' => 'mon', 'status' => 1]);
        $kg = Unit::create(['name' => 'Kilogram', 'code' => 'kg', 'is_base_unit' => true, 'status' => 1]);

        $conversionResponse = $this->postJson('/api/v1/unit-conversions', [
            'from_unit_id' => $mon->id,
            'to_unit_id' => $kg->id,
            'factor' => 40.0000,
            'notes' => 'Standard Shailkupa conversion: 1 mon = 40 kg',
            'status' => 1,
        ]);

        $conversionResponse->assertStatus(201)
            ->assertJsonPath('data.factor', 40);

        // Test direct conversion (5 mon -> 200 kg)
        $convertRes = $this->postJson('/api/v1/unit-conversions/convert', [
            'from_unit_id' => $mon->id,
            'to_unit_id' => $kg->id,
            'quantity' => 5,
        ]);

        $convertRes->assertStatus(200)
            ->assertJsonPath('data.converted_quantity', 200);

        // Test inverse conversion (80 kg -> 2 mon)
        $inverseRes = $this->postJson('/api/v1/unit-conversions/convert', [
            'from_unit_id' => $kg->id,
            'to_unit_id' => $mon->id,
            'quantity' => 80,
        ]);

        $inverseRes->assertStatus(200)
            ->assertJsonPath('data.converted_quantity', 2);
    }
}