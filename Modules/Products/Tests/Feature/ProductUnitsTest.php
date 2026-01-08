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
        /* arrange */
        $payload = ['unit_name' => 'Box'];
        $record  = ProductUnit::factory()
            ->for($this->company)
            ->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* assert */
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
        /* arrange */
        $payload = [
            'unit_name'      => 'Pack',
            'unit_name_plrl' => 'Packs',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
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
        /* arrange */
        $payload = [
            'unit_name' => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['unit_name']);

        $this->assertDatabaseMissing('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_unit_through_a_modal(): void
    {
        /* arrange */
        $productUnit = ProductUnit::factory()
            ->for($this->company)
            ->create(['unit_name' => 'Old Unit', 'unit_name_plrl' => 'kgs']);

        $payload = [
            'unit_name'      => 'Updated Unit',
            'unit_name_plrl' => 'Updated Units',
        ];

        /* act */
        Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction(TestAction::make('edit')->table($productUnit), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $this->assertDatabaseHas('product_units', array_merge($payload, [
            'id' => $productUnit->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_product_unit_through_a_modal_without_required_unit_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $record = ProductUnit::factory()->for($this->company)->create(['unit_name' => 'X']);

        $tenant  = Str::lower($this->company->search_code);
        $payload = ['unit_name' => null];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class, [
                'tenant' => $tenant,
            ])
            ->mountAction(TestAction::make('edit')->table($record), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
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
        /* arrange */
        $payload = [
            'unit_name'      => 'Pack',
            'unit_name_plrl' => 'Packs',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductUnit::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
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
        /* arrange */
        $payload = [
            'unit_name' => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductUnit::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['unit_name']);

        $this->assertDatabaseMissing('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_unit(): void
    {
        /* arrange */
        $record  = ProductUnit::factory()->for($this->company)->create(['unit_name' => 'Old Unit']);
        $payload = ['unit_name' => 'Updated Unit'];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProductUnit::class, ['record' => $record->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product_unit(): void
    {
        /* arrange */
        $productUnit = ProductUnit::factory()
            ->for($this->company)
            ->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction(TestAction::make('delete')->table($productUnit))
            ->callMountedAction();

        /* assert */
        $component->assertSuccessful();
        $this->assertModelMissing($productUnit);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_product_unit_twice(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

        /* arrange */
        $productUnit = ProductUnit::factory()->for($this->company)->create();
        $productUnit->delete();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductUnits::class)
            ->mountAction(TestAction::make('delete')->table($productUnit))
            ->callMountedAction();

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('product_units', ['id' => $productUnit->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
