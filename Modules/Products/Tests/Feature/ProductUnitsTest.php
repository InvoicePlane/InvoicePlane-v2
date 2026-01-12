<?php

namespace Modules\Products\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Products\Filament\Company\Resources\ProductUnits\Pages\CreateProductUnit;
use Modules\Products\Filament\Company\Resources\ProductUnits\Pages\EditProductUnit;
use Modules\Products\Filament\Company\Resources\ProductUnits\Pages\ListProductUnits;
use Modules\Products\Filament\Company\Resources\ProductUnits\ProductUnitResource;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductUnitResource::class)]
class ProductUnitsTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_product_units(): void
    {
        /* Arrange */
        $payload = ['unit_name' => 'Box'];
        $record  = ProductUnit::factory()
            ->for($this->company)
            ->create($payload);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
        $component->assertSuccessful()
            ->assertCanSeeTableRecords(collect([$record]));

        $this->assertDatabaseHas('product_units', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_a_product_unit_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'unit_name'      => 'Pack',
            'unit_name_plrl' => 'Packs',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *  'unit_name' => null
     * }
     */
    public function it_fails_to_create_product_unit_through_a_modal_without_required_unit_name(): void
    {
        /* Arrange */
        $payload = [
            'unit_name' => null,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['unit_name']);

        $this->assertDatabaseMissing('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_unit_through_a_modal(): void
    {
        /* Arrange */
        $productUnit = ProductUnit::factory()
            ->for($this->company)
            ->create(['unit_name' => 'Old Unit', 'unit_name_plrl' => 'kgs']);

        $payload = [
            'unit_name'      => 'Updated Unit',
            'unit_name_plrl' => 'Updated Units',
        ];

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction(TestAction::make('edit')->table($productUnit), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('product_units', array_merge($payload, [
            'id' => $productUnit->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_product_unit_through_a_modal_without_required_unit_name(): void
    {
        $this->markTestIncomplete();
        /* Arrange */
        $record = ProductUnit::factory()->for($this->company)->create(['unit_name' => 'X']);

        $tenant  = Str::lower($this->company->search_code);
        $payload = ['unit_name' => null];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class, [
                'tenant' => $tenant,
            ])
            ->mountAction(TestAction::make('edit')->table($record), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['unit_name']);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *  'unit_name' => 'Box'
     * }
     */
    public function it_creates_a_product_unit(): void
    {
        /* Arrange */
        $payload = [
            'unit_name'      => 'Pack',
            'unit_name_plrl' => 'Packs',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductUnit::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *  'unit_name' => null
     * }
     */
    public function it_fails_to_create_product_unit_without_required_unit_name(): void
    {
        /* Arrange */
        $payload = [
            'unit_name' => null,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductUnit::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['unit_name']);

        $this->assertDatabaseMissing('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_unit(): void
    {
        /* Arrange */
        $record  = ProductUnit::factory()->for($this->company)->create(['unit_name' => 'Old Unit']);
        $payload = ['unit_name' => 'Updated Unit'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProductUnit::class, ['record' => $record->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product_unit(): void
    {
        /* Arrange */
        $productUnit = ProductUnit::factory()
            ->for($this->company)
            ->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction(TestAction::make('delete')->table($productUnit))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $this->assertModelMissing($productUnit);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_product_unit_twice(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

        /* Arrange */
        $productUnit = ProductUnit::factory()->for($this->company)->create();
        $productUnit->delete();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction(TestAction::make('delete')->table($productUnit))
            ->callMountedAction();

        /* Assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('product_units', ['id' => $productUnit->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
