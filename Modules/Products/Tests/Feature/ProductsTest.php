<?php

namespace Modules\Products\Tests\Feature;

use Modules\Products\Models\ProductUnit;

use Modules\Products\Filament\Company\Resources\ProductResource\Pages\ListProducts;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Models\TaxRate;

use Modules\Products\Filament\Company\Resources\ProductResource;

use Modules\Products\Filament\Company\Resources\ProductResource\Pages\CreateProduct;

use Modules\Core\Models\User;

use Modules\Products\Models\ProductCategory;

use Modules\Products\Tests\Feature\ProductsTest;

use Modules\Products\Models\Product;

use Modules\Core\Models\Company;

use Modules\Products\Filament\Company\Resources\ProductResource\Pages\EditProduct;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Products\Filament\Company\Resources\ProductResource;
use Modules\Products\Filament\Company\Resources\ProductResource\Pages\CreateProduct;
use Modules\Products\Filament\Company\Resources\ProductResource\Pages\EditProduct;
use Modules\Products\Filament\Company\Resources\ProductResource\Pages\ListProducts;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductResource::class)]
class ProductsTest extends AbstractTestCase
{
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
    public function it_lists_items(): void
    {
        $this->markTestIncomplete();
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productCategory->family_id,
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
        Livewire::test(ListProducts::class)
            ->assertSuccessful()
            ->assertSee('::product_name::');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "standard",
     *   "code": "P001",
     *   "item_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_creates_a_product(): void
    {
        $this->markTestSkipped('Skipped test.');
        // $this->authenticated();
        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productCategory->family_id,
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

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "standard",
     *   "item_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_without_code(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'item_name' => '',
            ])
            ->call('create')
            ->assertHasErrors(['item_name', 'price', 'category_id']);
    }

    public function it_updates_a_product(): void
    {
        $this->markTestSkipped('Skipped test.');
        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productCategory->family_id,
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

        $product     = Product::factory()->create($payload);
        $updatedData = [
            'product_name'  => 'Updated Product',
            'product_price' => 70.00,
        ];

        Livewire::test(EditProduct::class, ['record' => $product->product_id])
            ->set('data.`product_name`', $updatedData['product_name'])
            ->set('data.product_price', $updatedData['product_price'])
            ->call('save');

        $this->assertDatabaseHas('products', array_merge($updatedData, [
            'product_id' => $product->product_id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "category_id": "Value",
     * "unit_id": "Value",
     * "tax_rate_id": "Value",
     * "type": "Value",
     * "code": "Example",
     * "item_name": "Example",
     * "price": "9.99",
     * "cost_price": "9.99",
     * "tariff": "Example",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_item_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Product::factory()->create();

        $payload = [
            'company_id'  => 'Value',
            'category_id' => 'Value',
            'unit_id'     => 'Value',
            'tax_rate_id' => 'Value',
            'type'        => 'Value',
            'code'        => 'Example',
            'item_name'   => 'Example',
            'price'       => 9.99,
            'cost_price'  => 9.99,
            'tariff'      => 'Example',
            'description' => 'Example',
        ];

        Livewire::test(EditProduct::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    public function it_deletes_a_product(): void
    {
        $this->markTestIncomplete('Needs delete action');

        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'           => $productCategory->family_id,
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

        Livewire::test(ListProducts::class)
            ->callTableAction('delete', $product)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('products', [
            'product_id' => $product->product_id,
        ]);
    }

    public function it_bulk_deletes_products(): void
    {
        // $this->authenticated();
        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'family_id'   => $productCategory->family_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $products = Product::factory(3)->create($payload);

        Livewire::test(ListProducts::class)
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
        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $payload = [
            'family_id'   => $productCategory->family_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $product1 = Product::factory()->create($payload);

        Livewire::test(ListProducts::class)
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
        $productCategory = ProductCategory::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $payload = [
            'family_id'   => $productCategory->family_id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $product = Product::factory()->create($payload);

        Livewire::test(ListProducts::class)
            ->callTableAction('processSelections', $product)
            ->assertHasNoErrors();
    }
    // endregion
}
