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
    public function it_lists_product_units(): void
    {
        // arrange
        $record = ProductUnit::factory()->for($this->user->company)->create(['name' => 'Box']);

        // act + assert
        Livewire::test(ListProductUnits::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Pack']
     */
    public function it_creates_a_product_unit(): void
    {
        // arrange
        $payload = ['name' => 'Pack'];

        // act
        Livewire::test(CreateProductUnit::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_product_unit_without_name(): void
    {
        // arrange
        $payload = [];

        // act
        Livewire::test(CreateProductUnit::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Updated Unit']
     */
    public function it_updates_a_product_unit(): void
    {
        // arrange
        $record  = ProductUnit::factory()->for($this->user->company)->create(['name' => 'Old Unit']);
        $payload = ['name' => 'Updated Unit'];

        // act
        Livewire::test(EditProductUnit::class, ['record' => $record->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_units', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => null]
     */
    public function it_fails_to_update_product_unit_with_null_name(): void
    {
        // arrange
        $record  = ProductUnit::factory()->for($this->user->company)->create(['name' => 'X']);
        $payload = ['name' => null];

        // act
        Livewire::test(EditProductUnit::class, ['record' => $record->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_product_unit(): void
    {
        // arrange
        $record = ProductUnit::factory()->for($this->user->company)->create();

        // act
        Livewire::test(ListProductUnits::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('product_units', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_product_unit_twice(): void
    {
        // arrange
        $record = ProductUnit::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        Livewire::test(ListProductUnits::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record)
            ->assertHasErrors();

        $this->assertDatabaseMissing('product_units', ['id' => $record->id]);
    }
}
