<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
    public function it_lists_product_categories(): void
    {
        $productCategory = ProductCategory::factory()->create();
        //$this->actingAs(User::factory()->create());

        Livewire::test(ListProductCategories::class)
            ->assertSuccessful()
            ->assertSee($productCategory->category_name);
    }

    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Products\Filament\Company\Resources\ProductCategoryResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "category_name": "Example",
     * "description": "Example"
     * }
     */
    public function it_fails_to_create_productcategory_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'    => 'Value',
            'category_name' => 'Example',
            'description'   => 'Example',
        ];

        Livewire::test(CreateProductCategory::class)
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
     * \Modules\Products\Filament\Company\Resources\ProductCategoryResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "category_name": "Example",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_productcategory_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = ProductCategory::factory()->create();

        $payload = [
            'company_id'    => 'Value',
            'category_name' => 'Example',
            'description'   => 'Example',
        ];

        Livewire::test(EditProductCategory::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }
}
