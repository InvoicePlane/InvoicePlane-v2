<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Products\Filament\Company\Resources\ProductUnitResource;
use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages\CreateProductUnit;
use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages\EditProductUnit;
use Modules\Products\Filament\Company\Resources\ProductUnitResource\Pages\ListProductUnits;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductUnitResource::class)]

class ProductUnitsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_product_units(): void
    {
        $payload = [
            'unit_name'      => '::example_unit::',
            'unit_name_plrl' => '::example_units::',
        ];
        ProductUnit::factory()->create($payload);

        Livewire::test(ListProductUnits::class)
            ->assertSee('::example_unit::')
            ->assertSee('::example_units::');
    }

    // endregion

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

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * Missing Required Fields:
     * - unit_name
     * @payload
     * {
     * "company_id": "Value",
     * "unit_name_plrl": "Example"
     * }
     */
    public function it_fails_to_create_product_unit_without_required_unit_name(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());
        /**
         * Missing Required Fields:
         * - unit_name
         */
        $payload = [
            'company_id'     => 'Value',
            'unit_name'      => 'Example',
            'unit_name_plrl' => 'Example',
        ];

        Livewire::test(CreateProductUnit::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    public function it_updates_a_product_unit(): void
    {
        $this->markTestIncomplete();
        $productUnit = ProductUnit::factory()->create();

        $payload = [
            'unit_name' => 'Meter',
        ];

        Livewire::test(EditProductUnit::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        $response->assertStatus(200);
        $this->assertDatabaseHas('product_units', $payload);
    }


    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "unit_name": "Example",
     * "unit_name_plrl": "Example"
     * }
     */
    public function it_fails_to_update_productunit_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = ProductUnit::factory()->create();

        $payload = [
            'company_id'     => 'Value',
            'unit_name'      => 'Example',
            'unit_name_plrl' => 'Example',
        ];

        Livewire::test(EditProductUnit::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    public function it_deletes_a_product_unit(): void
    {
        $this->markTestIncomplete('needs delete action');
        $productUnit = ProductUnit::factory()->create();

        $response = $this->delete(route('filament.ivpl.resources.filament.resources.product-units.destroy', $productUnit->product_unit_id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('product_units', ['product_unit_id' => $productUnit->product_unit_id]);
    }
    // endregion
}
