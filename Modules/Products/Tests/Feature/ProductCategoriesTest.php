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
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_product_categories(): void
    {
        ProductCategory::factory()->create([
            'family_name' => '::product_family_name::',
        ]);

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListProductCategories::class)
            ->assertSuccessful()
            ->assertSee($productCategory->category_name);
    }

    // endregion

    // region crud
    /**
     * @test
     * Payload:
     * {
     * "family_name": "::product_family_name::"
     * }
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_product_category(): void
    {
        $this->markTestSkipped('something about a view');
        $payload = [
            'family_name' => '::product_family_name::',
        ];

        Livewire::test(CreateProductFamily::class)
            ->set('data.family_name', $payload['family_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_families', array_merge($payload, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "category_name": null,
     * "description": "Example"
     * }
     */
    public function it_fails_to_create_a_product_family_without_category_name(): void
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

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for updating a product family:
     *
     *
     *            [
     *            'family_name' => 'Updated Family',
     *            ]
     */
    public function it_updates_a_product_family(): void
    {
        $this->markTestIncomplete();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::original_product_family_name::',
        ]);

        $updatedData = [
            'family_name' => '::updated_product_family_name::',
        ];

        Livewire::test(EditProductFamily::class, ['record' => $productFamily->family_id])
            ->set('data.family_name', $updatedData['family_name'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_families', array_merge($updatedData, [
            'family_id' => $productFamily->family_id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
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

    public function it_deletes_a_product_family(): void
    {
        $this->markTestIncomplete('needs delete action');
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::product_family_name::',
        ]);

        Livewire::test(ManageProductFamilies::class)
            ->callTableAction('delete', $productFamily)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('product_families', [
            'family_id' => $productFamily->family_id,
        ]);
    }
    // endregion
}
