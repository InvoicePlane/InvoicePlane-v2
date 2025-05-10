<?php

namespace Modules\Products\Tests\Feature;

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
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Box']
     */
    #[Group('crud')]
    public function it_lists_product_units(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductUnit::factory()->for($this->user->companies()->first())->create(['name' => 'Box']);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProductUnits::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_product_unit(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = ['name' => 'Pack'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateProductUnit::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_product_unit_without_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = [];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateProductUnit::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_unit(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductUnit::factory()->for($this->user->companies()->first())->create(['name' => 'Old Unit']);
        $payload = ['name' => 'Updated Unit'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditProductUnit::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_product_unit_with_null_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductUnit::factory()->for($this->user->companies()->first())->create(['name' => 'X']);
        $payload = ['name' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditProductUnit::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product_unit(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductUnit::factory()->for($this->user->companies()->first())->create();

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProductUnits::class)->callTableAction('delete', $record);

        // assert
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

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProductUnits::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('product_units', ['id' => $record->id]);
    }
}
