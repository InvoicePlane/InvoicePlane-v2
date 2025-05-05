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
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_product_units(): void
    {
        $productUnit = ProductUnit::factory()->create();
        //$this->actingAs(User::factory()->create());

        Livewire::test(ListProductUnits::class)
            ->assertSuccessful()
            ->assertSee($productUnit->unit_name);
    }

    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Products\Filament\Company\Resources\ProductUnitResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "unit_name": "Example",
     * "unit_name_plrl": "Example"
     * }
     */
    public function it_fails_to_create_productunit_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

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

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Products\Filament\Company\Resources\ProductUnitResource.
     *
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
}
