<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\TaxRate;
use Modules\Core\tests\AbstractTestCase;
use Modules\Products\Filament\Resources\ProductResource\Pages\CreateProduct;
use Modules\Products\Filament\Resources\ProductResource\Pages\EditProduct;
use Modules\Products\Filament\Resources\ProductResource\Pages\ManageProducts;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductFamily;
use Modules\Products\Models\ProductUnit;

class ProductsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    // endregion

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /** @test */
    public function it_shows_products_index(): void
    {
        // $this->authenticated();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productFamily->family_id,
            'product_sku'         => 'TESTSKU',
            'product_name'        => '::product_name::',
            'product_description' => 'A test description for the product.',
            'product_price'       => 25.50,
            'purchase_price'      => 15.00,
            'provider_name'       => 'Test Provider',
            'tax_rate_id'         => $taxRate->tax_rate_id,
            'unit_id'             => $productUnit->unit_id,
            'product_tariff'      => 12345,
        ];
        $product = Product::factory()->create($payload);
        Livewire::test(ManageProducts::class)
            ->assertStatus(200)
            ->assertSee('::product_name::');
    }

    /** @test */
    public function it_creates_a_product(): void
    {
        $this->markTestSkipped('Skipped test.');
        // $this->authenticated();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productFamily->family_id,
            'product_sku'         => 'TESTSKU',
            'product_name'        => '::product_name::',
            'product_description' => 'A test description for the product.',
            'product_price'       => 25.50,
            'purchase_price'      => 15.00,
            'provider_name'       => 'Test Provider',
            'tax_rate_id'         => $taxRate->tax_rate_id,
            'unit_id'             => $productUnit->unit_id,
            'product_tariff'      => 12345,
        ];

        $product = Product::factory()->create($payload);

        $component = Livewire::test(CreateProduct::class)
            ->set('data.family_id', $payload['family_id'])
            ->set('data.product_sku', $payload['product_sku'])
            ->set('data.product_name', $payload['product_name'])
            ->set('data.product_description', $payload['product_description'])
            ->set('data.product_price', $payload['product_price'])
            ->set('data.provider_name', $payload['provider_name'])
            ->set('data.tax_rate_id', $payload['tax_rate_id'])
            ->set('data.unit_id', $payload['unit_id'])
            ->set('data.product_tariff', $payload['product_tariff'])
            ->call('create');
        dd($component->get('data'));

        $this->assertDatabaseHas('products', $payload);
    }

    /** @test */
    public function it_updates_a_product(): void
    {
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productFamily->family_id,
            'product_sku'         => 'TESTSKU',
            'product_name'        => '::product_name::',
            'product_description' => 'A test description for the product.',
            'product_price'       => 25.50,
            'purchase_price'      => 15.00,
            'provider_name'       => 'Test Provider',
            'tax_rate_id'         => $taxRate->tax_rate_id,
            'unit_id'             => $productUnit->unit_id,
            'product_tariff'      => 12345,
        ];

        $product = Product::factory()->create($payload);
        $updatedData = [
            'product_name'  => 'Updated Product',
            'product_price' => 70.00,
        ];

        Livewire::test(EditProduct::class, ['record' => $product->product_id])
            ->set('data.`product_name`', $updatedData['product_name'])
            ->set('data.product_price', $updatedData['product_price'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', array_merge($updatedData, [
            'product_id' => $product->product_id,
        ]));
    }

    /** @test */
    public function it_deletes_a_client(): void
    {
        $this->markTestIncomplete('Needs delete action');

        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productFamily->family_id,
            'product_sku'         => 'TESTSKU',
            'product_name'        => '::product_name::',
            'product_description' => 'A test description for the product.',
            'product_price'       => 25.50,
            'purchase_price'      => 15.00,
            'provider_name'       => 'Test Provider',
            'tax_rate_id'         => $taxRate->tax_rate_id,
            'unit_id'             => $productUnit->unit_id,
            'product_tariff'      => 12345,
        ];

        $product = Product::factory()->create($payload);

        Livewire::test(ManageProducts::class)
            ->callTableAction('delete', $product)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('products', [
            'product_id' => $product->product_id,
        ]);
    }

    /** @test */
    public function it_bulk_deletes_products(): void
    {
        // $this->authenticated();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'   => $productFamily->family_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $products = Product::factory(3)->create($payload);

        Livewire::test(ManageProducts::class)
            ->callTableBulkAction('delete', $products)
            ->assertHasNoErrors();

        foreach ($products as $product) {
            $this->assertDatabaseMissing('products', [
                'product_id' => $product->product_id,
            ]);
        }
    }
    // endregion

    // region Spicy Tests

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.products.process_selections')
     *
     * @skip Not implemented yet
     **/
    public function it_products_process_selections(): void
    {
        $this->marktestskipped('Skipped test.');
        // $this->authenticate();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $payload = [
            'family_id'   => $productFamily->family_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $product1 = Product::factory()->create($payload);

        Livewire::test(ManageProducts::class)
            ->callTableAction('processSelections', $product1)
            ->assertHasNoErrors();
    }

    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.products.process_selections')
     *
     * @skip Not implemented yet
     **/
    public function it_fails_to_process_selections_without_product_ids(): void
    {
        $this->marktestskipped('Skipped test.');
        // $this->authenticate();
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $payload = [
            'family_id'   => $productFamily->family_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $product = Product::factory()->create($payload);

        Livewire::test(ManageProducts::class)
            ->callTableAction('processSelections', $product)
            ->assertHasNoErrors();
    }
    // endregion
}
