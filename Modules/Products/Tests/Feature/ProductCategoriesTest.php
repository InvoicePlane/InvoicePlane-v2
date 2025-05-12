<?php

namespace Modules\Products\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\CreateProductCategory;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\EditProductCategory;
use Modules\Products\Filament\Company\Resources\ProductCategoryResource\Pages\ListProductCategories;
use Modules\Products\Models\ProductCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductCategoryResource::class)]
class ProductCategoriesTest extends AbstractCompanyPanelTestCase
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
        $payload = [
            'name' => 'Hardware',
        ];

        $record = ProductCategory::factory()->for($this->user->companies()->first())->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductCategories::class);

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_product_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $payload = [
            'name' => 'Office Supplies',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: name
     * {
     *   "name": ""
     * }
     */
    public function it_fails_to_create_product_category_without_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $payload = [];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductCategory::factory()->for($this->user->companies()->first())->create(['name' => 'Old Cat']);
        $payload = ['name' => 'Updated Category'];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditProductCategory::class, ['record' => $record->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: name
     * {
     *   "name": ""
     * }
     */
    public function it_fails_to_update_category_with_missing_name(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record  = ProductCategory::factory()->for($this->user->companies()->first())->create(['name' => 'Valid']);
        $payload = ['name' => null];

        /* act */
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

        $record = ProductCategory::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListProductCategories::class)->callTableAction('delete', $record);

        /* assert */
        $this->assertDatabaseMissing('product_categories', ['id' => $record->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_already_deleted_category(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $record = ProductCategory::factory()->for($this->user->companies()->first())->create();
        $record->delete();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListProductCategories::class)->callTableAction('delete', $record);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('product_categories', ['id' => $record->id]);
    }
}
