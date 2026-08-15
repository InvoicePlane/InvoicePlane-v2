<?php

namespace Modules\Products\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Products\Filament\Company\Resources\ProductCategories\Pages\CreateProductCategory;
use Modules\Products\Filament\Company\Resources\ProductCategories\Pages\EditProductCategory;
use Modules\Products\Filament\Company\Resources\ProductCategories\Pages\ListProductCategories;
use Modules\Products\Models\ProductCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListProductCategories::class)]
class ProductCategoriesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['category_name' => 'Hardware']
     */
    public function it_lists_product_categories(): void
    {
        /* Arrange */
        $payload = [
            'category_name' => 'Hardware',
        ];

        $record = ProductCategory::factory()
            ->for($this->company)
            ->create($payload);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductCategories::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
        $component->assertSuccessful()
            ->assertCanSeeTableRecords(collect([$record]));

        $this->assertDatabaseHas('product_categories', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_a_product_category_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'category_name' => 'Office Supplies',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductCategories::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('product_categories', array_merge(
            ['company_id' => $this->company->id],
            $payload
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: category_name
     * {
     *   "category_description": "Missing required name"
     * }
     */
    public function it_fails_to_create_product_category_through_a_modal_without_required_name(): void
    {
        /* Arrange */
        $payload = [
            'category_name' => null,
        ];

        /* act & assert */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductCategories::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component
            ->assertHasFormErrors(['category_name']);

        $this->assertDatabaseMissing('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_category_through_a_modal(): void
    {
        /* Arrange */
        $productCategory = ProductCategory::factory()
            ->for($this->company)
            ->create(['category_name' => 'Old Cat']);

        $payload = ['category_name' => 'Updated Category'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductCategories::class)
            ->mountAction(TestAction::make('edit')->table($productCategory), $payload)
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component
            ->assertSuccessful();

        $this->assertDatabaseHas('product_categories', array_merge(
            ['id' => $productCategory->id],
            $payload
        ));
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "category_name": "Electronics",
     *   "category_description": "All electronic items"
     * }
     */
    public function it_creates_a_product_category(): void
    {
        /* Arrange */
        $payload = [
            'category_name' => 'Office Supplies',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: category_name
     * {
     *   "category_description": "Missing required name"
     * }
     */
    public function it_fails_to_create_product_category_without_required_category_name(): void
    {
        /* Arrange */
        $payload = [
            'category_name' => null,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProductCategory::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['category_name']);

        $this->assertDatabaseMissing('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_category(): void
    {
        /* Arrange */
        $record  = ProductCategory::factory()->for($this->company)->create(['category_name' => 'Old Cat']);
        $payload = ['category_name' => 'Updated Category'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProductCategory::class, ['record' => $record->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('product_categories', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product_category(): void
    {
        /* Arrange */
        $productCategory = ProductCategory::factory()
            ->for($this->company)
            ->create(['category_name' => 'Category to Delete']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProductCategories::class)
            ->mountAction(TestAction::make('delete')->table($productCategory))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('product_categories', ['id' => $productCategory->id]);
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_confirms_deleted_category_is_no_longer_findable(): void
    {
        /* Arrange */
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $id              = $productCategory->id;

        /* Act */
        $productCategory->delete();

        /* Assert — hard delete: record is gone from DB and cannot be retrieved */
        $this->assertDatabaseMissing('product_categories', ['id' => $id]);
        $this->assertNull(ProductCategory::find($id));
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
