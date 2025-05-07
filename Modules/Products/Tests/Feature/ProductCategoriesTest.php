<?php

namespace Modules\Products\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\CreateProductCategory;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\EditProductCategory;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\ListProductCategories;
use Modules\Products\Models\ProductCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductCategoryResource::class)]
class ProductCategoriesTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Hardware']
     */
    public function it_lists_product_categories(): void
    {
        // arrange
        $record = ProductCategory::factory()->for($this->user->company)->create(['name' => 'Hardware']);

        // act + assert
        Livewire::test(ListProductCategories::class)
            ->actingAs($this->user)
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Office Supplies']
     */
    public function it_creates_a_product_category(): void
    {
        // arrange
        $payload = ['name' => 'Office Supplies'];

        // act
        Livewire::test(CreateProductCategory::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_product_category_without_name(): void
    {
        // arrange
        $payload = [];

        // act
        Livewire::test(CreateProductCategory::class)
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Updated Category']
     */
    public function it_updates_a_product_category(): void
    {
        // arrange
        $record  = ProductCategory::factory()->for($this->user->company)->create(['name' => 'Old Cat']);
        $payload = ['name' => 'Updated Category'];

        // act
        Livewire::test(EditProductCategory::class, ['record' => $record->id])
            ->actingAs($this->user)
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => null]
     */
    public function it_fails_to_update_category_with_null_name(): void
    {
        // arrange
        $record  = ProductCategory::factory()->for($this->user->company)->create(['name' => 'Valid']);
        $payload = ['name' => null];

        // act
        Livewire::test(EditProductCategory::class, ['record' => $record->id])
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
    public function it_deletes_a_product_category(): void
    {
        // arrange
        $record = ProductCategory::factory()->for($this->user->company)->create();

        // act
        Livewire::test(ListProductCategories::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('product_categories', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_already_deleted_category(): void
    {
        // arrange
        $record = ProductCategory::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        Livewire::test(ListProductCategories::class)
            ->actingAs($this->user)
            ->callTableAction('delete', $record)
            ->assertHasErrors();

        $this->assertDatabaseMissing('product_categories', ['id' => $record->id]);
    }
}
