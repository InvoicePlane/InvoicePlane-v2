<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Products\Filament\Company\Resources\Products\Pages\ListProducts;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductResource::class)]
class ProductCategoryFilterTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('crud')]
    public function it_filters_products_by_category(): void
    {
        /* Arrange */
        $hardware = ProductCategory::factory()->for($this->company)->create([
            'category_name' => 'Hardware',
        ]);
        $software = ProductCategory::factory()->for($this->company)->create([
            'category_name' => 'Software',
        ]);

        $hardwareProduct = Product::factory()->for($this->company)->create([
            'category_id'  => $hardware->id,
            'product_name' => 'Hardware Product',
        ]);
        $softwareProduct = Product::factory()->for($this->company)->create([
            'category_id'  => $software->id,
            'product_name' => 'Software Product',
        ]);

        /* Act + Assert */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$hardwareProduct, $softwareProduct])
            ->filterTable('category_id', $hardware->id)
            ->assertCanSeeTableRecords([$hardwareProduct])
            ->assertCanNotSeeTableRecords([$softwareProduct]);
    }

    #[Test]
    #[Group('crud')]
    public function it_filters_products_by_unit(): void
    {
        /* Arrange */
        $piece = ProductUnit::factory()->for($this->company)->create([
            'unit_name' => 'Piece',
        ]);
        $hour = ProductUnit::factory()->for($this->company)->create([
            'unit_name' => 'Hour',
        ]);

        $pieceProduct = Product::factory()->for($this->company)->create([
            'unit_id'      => $piece->id,
            'product_name' => 'Piece Product',
        ]);
        $hourProduct = Product::factory()->for($this->company)->create([
            'unit_id'      => $hour->id,
            'product_name' => 'Hour Product',
        ]);

        /* Act + Assert */
        Livewire::actingAs($this->user)
            ->test(ListProducts::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$pieceProduct, $hourProduct])
            ->filterTable('unit_id', $piece->id)
            ->assertCanSeeTableRecords([$pieceProduct])
            ->assertCanNotSeeTableRecords([$hourProduct]);
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_offers_categories_of_the_current_tenant_as_filter_options(): void
    {
        /* Arrange */
        $ownCategory = ProductCategory::factory()->for($this->company)->create([
            'category_name' => 'Own Category',
        ]);
        $companyB        = \Modules\Core\Models\Company::factory()->create();
        $foreignCategory = ProductCategory::factory()->for($companyB)->create([
            'category_name' => 'Foreign Category',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertSuccessful();

        /* Assert — filter options are tenant-scoped by the BelongsToCompany global scope */
        $options = $component->instance()
            ->getTable()
            ->getFilter('category_id')
            ->getOptions();

        $this->assertContains('Own Category', $options);
        $this->assertNotContains('Foreign Category', $options);
    }
}
