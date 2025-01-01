<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\tests\AbstractTestCase;
use Modules\Products\Filament\Resources\ProductUnitResource\Pages\CreateProductUnit;
use Modules\Products\Filament\Resources\ProductUnitResource\Pages\ManageProductUnits;
use Modules\Products\Models\ProductUnit;

class ProductUnitsTest extends AbstractTestCase
{
    use RefreshDatabase;

    // region CRUD Tests
    /** @test */
    public function it_lists_product_units(): void
    {
        $payload = [
            'unit_name'      => '::example_unit::',
            'unit_name_plrl' => '::example_units::',
        ];
        ProductUnit::factory()->create($payload);

        Livewire::test(ManageProductUnits::class)
            ->assertStatus(200)
            ->assertSee('::example_unit::')
            ->assertSee('::example_units::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_product_unit(): void
    {
        $this->markTestSkipped('Not implemented.');
        /**
         * Payload:
         * {
         *     "unit_name": "example_unit",
         *     "unit_name_plrl": "example_units"
         * }
         */
        $payload = [
            'unit_name'      => '::example_unit::',
            'unit_name_plrl' => '::example_units::',
        ];

        Livewire::test(CreateProductUnit::class)
            ->set('data.unit_name', $payload['unit_name'])
            ->set('data.unit_name_plrl', $payload['unit_name_plrl'])
            ->call('create');

        $this->assertDatabaseHas('units', $payload);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_a_product_unit_without_unit_name(): void
    {
        /**
         * Missing Required Fields:
         * - unit_name
         */
        $payload = [
            'unit_name'      => null,
            'unit_name_plrl' => 'example_units',
        ];

        $response = $this->post(route('filament.ivpl.resources.filament.resources.product-units.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['unit_name']);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_product_unit(): void
    {
        $productUnit = ProductUnit::factory()->create();

        $payload = [
            'unit_name' => 'Meter',
        ];

        $response = $this->put(route('filament.ivpl.resources.filament.resources.product-units.update', $productUnit->product_unit_id), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('product_units', $payload);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_product_unit(): void
    {
        $productUnit = ProductUnit::factory()->create();

        $response = $this->delete(route('filament.ivpl.resources.filament.resources.product-units.destroy', $productUnit->product_unit_id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('product_units', ['product_unit_id' => $productUnit->product_unit_id]);
    }

    // endregion
}
