<?php

namespace Modules\Products\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\User;
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
    protected User $user;

    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_product_units(): void
    {
        /* arrange */
        $payload = ['unit_name' => 'Box'];
        $record  = ProductUnit::factory()->for($this->user->companies()->first())->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListProductUnits::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('product_units', $payload);
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
        $this->markTestIncomplete();

        /* arrange */
        $payload = ['unit_name' => 'Pack'];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductUnit::class, ['state' => $payload])  // Inject state on mount
            ->call('handleCreate');

        dd($component->instance()->getPublicMethods());
        dd($component->instance()->getActions());
        dd($component->instance());

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
        $this->markTestIncomplete();

        /* arrange */
        $payload = [];

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
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductUnit::factory()->for($this->user->companies()->first())->create(['unit_name' => 'Old Unit']);
        $payload = ['unit_name' => 'Updated Unit'];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditProductUnit::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_product_unit_with_null_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductUnit::factory()->for($this->user->companies()->first())->create(['unit_name' => 'X']);
        $payload = ['unit_name' => null];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditProductUnit::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['unit_name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product_unit(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductUnit::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListProductUnits::class)->callTableAction('delete', $record);

        /* assert */
        $this->assertDatabaseMissing('product_units', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_product_unit_twice(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductUnit::factory()->for($this->user->companies()->first())->create();
        $record->delete();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListProductUnits::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('product_units', ['id' => $record->id]);
    }
    # endregion
}
