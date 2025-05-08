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
    #[Group('crud')]
    public function it_lists_product_categories(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductCategory::factory()->for($this->user->company)->create(['name' => 'Hardware']);

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProductCategories::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($record);
    }

    #[Test]


#[Group('crud')]


    public function it_creates_a_product_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = ['name' => 'Office Supplies'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateProductCategory::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]


#[Group('crud')]


    public function it_fails_to_create_product_category_without_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = [];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateProductCategory::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]


#[Group('crud')]


    public function it_updates_a_product_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductCategory::factory()->for($this->user->company)->create(['name' => 'Old Cat']);
        $payload = ['name' => 'Updated Category'];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditProductCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]


#[Group('crud')]


    public function it_fails_to_update_category_with_null_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductCategory::factory()->for($this->user->company)->create(['name' => 'Valid']);
        $payload = ['name' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditProductCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]


#[Group('crud')]


    public function it_deletes_a_product_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductCategory::factory()->for($this->user->company)->create();

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProductCategories::class)->callTableAction('delete', $record);

        // assert
        $this->assertDatabaseMissing('product_categories', ['id' => $record->id]);
    }

    #[Test]


#[Group('crud')]


    public function it_fails_to_delete_already_deleted_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductCategory::factory()->for($this->user->company)->create();
        $record->delete();

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListProductCategories::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('product_categories', ['id' => $record->id]);
    }
}
