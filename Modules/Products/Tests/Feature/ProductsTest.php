<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Core\Tests\TestDecimal;
use Modules\Products\Enums\ProductType;
use Modules\Products\Filament\Company\Resources\Products\Pages\CreateProduct;
use Modules\Products\Filament\Company\Resources\Products\Pages\EditProduct;
use Modules\Products\Filament\Company\Resources\Products\Pages\ListProducts;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProductResource::class)]
class ProductsTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_products(): void
    {
        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'code'           => 'SKU-001',
            'product_name'   => 'Test Product',
            'price'          => 9.99,
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];
        $product = Product::factory()->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        /* assert */
        $component
            ->assertSuccessful();

        $this->assertDatabaseHas('products', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "code": "P001",
     *   "product_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_creates_a_product_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'category_id'  => $productCategory->id,
            'unit_id'      => $productUnit->id,
            'type'         => ProductType::PRODUCT->value,
            'code'         => 'SKU-001',
            'product_name' => 'Test Product',
            'price'        => 9.99,
            'tax_rate_id'  => $taxRate->id,
            'description'  => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', array_merge(
            $payload,
            ['price' => TestDecimal::exact(9.99)]
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: code
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "product_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_through_a_modal_without_required_code(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'product_name'   => 'Test Product',
            'price'          => 9.99,
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['code']);

        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: name
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "code": "P001",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_through_a_modal_without_required_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        /* arrange */
        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'code'           => 'SKU-001',
            'price'          => 9.99,
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: price
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "code": "P001",
     *   "product_name": "Test Product",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_through_a_modal_without_required_price(): void
    {
        $this->markTestIncomplete();

        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        /* arrange */
        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'code'           => 'SKU-001',
            'product_name'   => 'Test Product',
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['price']);

        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'product_sku'    => 'TESTSKU',
            'product_name'   => '::product_name::',
            'description'    => 'A test description for the product.',
            'product_price'  => 25.50,
            'purchase_price' => 15.00,
            'provider_name'  => 'Test Provider',
            'tax_rate_id'    => $taxRate->tax_rate_id,
            'unit_id'        => $productUnit->unit_id,
            'product_tariff' => 12345,
        ];

        $product     = Product::factory()->create($payload);
        $updatedData = [
            'product_name'  => 'Updated Product',
            'product_price' => 70.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class, ['record' => $product->product_id])
            ->mountAction('edit', ['record' => $product->product_id])
            ->fillForm($updatedData)
            ->callMountedAction();

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
     * "product_name": "Example",
     * "price": "9.99",
     * "cost_price": "9.99",
     * "tariff": "Example",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_product_through_a_modal_without_required_fields(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'product_sku'    => 'TESTSKU',
            'product_name'   => '::product_name::',
            'description'    => 'A test description for the product.',
            'product_price'  => 25.50,
            'purchase_price' => 15.00,
            'provider_name'  => 'Test Provider',
            'tax_rate_id'    => $taxRate->tax_rate_id,
            'unit_id'        => $productUnit->unit_id,
            'product_tariff' => 12345,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasNoFormErrors();
        $this->assertDatabaseMissing('products', $payload);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "code": "P001",
     *   "product_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_creates_a_product(): void
    {
        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'category_id'  => $productCategory->id,
            'unit_id'      => $productUnit->id,
            'type'         => ProductType::PRODUCT->value,
            'code'         => 'SKU-001',
            'product_name' => 'Test Product',
            'price'        => 9.99,
            'tax_rate_id'  => $taxRate->id,
            'description'  => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProduct::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', array_merge(
            $payload,
            ['price' => TestDecimal::exact(9.99)]
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: code
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "product_name": "Test Product",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_without_required_code(): void
    {
        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'product_name'   => 'Test Product',
            'price'          => 9.99,
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProduct::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['code']);

        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: name
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "code": "P001",
     *   "price": "9.99",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_without_required_name(): void
    {
        $this->markTestIncomplete();

        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        /* arrange */
        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'code'           => 'SKU-001',
            'price'          => 9.99,
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProduct::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: price
     * {
     *   "company_id": 1,
     *   "category_id": 2,
     *   "unit_id": 3,
     *   "tax_rate_id": 4,
     *   "type": "PRODUCT",
     *   "code": "P001",
     *   "product_name": "Test Product",
     *   "cost_price": "5.00",
     *   "tariff": "TX123",
     *   "description": "Example description"
     * }
     */
    public function it_fails_to_create_product_without_required_price(): void
    {
        $this->markTestIncomplete();

        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        /* arrange */
        $payload = [
            'category_id'    => $productCategory->id,
            'unit_id'        => $productUnit->id,
            'type'           => ProductType::PRODUCT->value,
            'code'           => 'SKU-001',
            'product_name'   => 'Test Product',
            'cost_price'     => 5.00,
            'product_tariff' => 123,
            'tax_rate_id'    => $taxRate->id,
            'description'    => 'Example',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateProduct::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['price']);

        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'product_sku'    => 'TESTSKU',
            'product_name'   => '::product_name::',
            'description'    => 'A test description for the product.',
            'product_price'  => 25.50,
            'purchase_price' => 15.00,
            'provider_name'  => 'Test Provider',
            'tax_rate_id'    => $taxRate->tax_rate_id,
            'unit_id'        => $productUnit->unit_id,
            'product_tariff' => 12345,
        ];

        $product     = Product::factory()->create($payload);
        $updatedData = [
            'product_name'  => 'Updated Product',
            'product_price' => 70.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProduct::class, ['record' => $product->product_id])
            ->fillForm($updatedData)
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
     * "product_name": "Example",
     * "price": "9.99",
     * "cost_price": "9.99",
     * "tariff": "Example",
     * "description": "Example"
     * }
     */
    public function it_fails_to_update_item_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'product_sku'    => 'TESTSKU',
            'product_name'   => '::product_name::',
            'description'    => 'A test description for the product.',
            'product_price'  => 25.50,
            'purchase_price' => 15.00,
            'provider_name'  => 'Test Provider',
            'tax_rate_id'    => $taxRate->tax_rate_id,
            'unit_id'        => $productUnit->unit_id,
            'product_tariff' => 12345,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->isLocal()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasNoFormErrors();
        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs delete action');

        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id'    => $productCategory->id,
            'product_sku'    => 'TESTSKU',
            'product_name'   => '::product_name::',
            'description'    => 'A test description for the product.',
            'product_price'  => 25.50,
            'purchase_price' => 15.00,
            'provider_name'  => 'Test Provider',
            'tax_rate_id'    => $taxRate->tax_rate_id,
            'unit_id'        => $productUnit->unit_id,
            'product_tariff' => 12345,
        ];

        $product = Product::factory()->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('delete', $product);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('products', [
            'product_id' => $product->product_id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_bulk_deletes_products(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // $this->authenticated();
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $payload = [
            'category_id' => $productCategory->id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $products = Product::factory(3)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('bulkDelete', $products);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        foreach ($products as $product) {
            $this->assertDatabaseMissing('products', [
                'product_id' => $product->product_id,
            ]);
        }
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_products_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s product.');

        /* arrange */
        // Create two different companies
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        // Create a user in company1
        $user1 = User::factory()->create();
        $user1->companies()->attach($company1);

        // Create a product for company2
        $product = Product::factory()->for($company2)->create();

        /* act */
        // Try to access the product as user1 (different company)
        $response = $this->actingAs($user1)
            ->get(route('filament.company.resources.products.index'));

        /* assert */
        // Verify access is denied (403 Forbidden or 404 Not Found)
        $response->assertStatus(403); // or 404, depending on your implementation
    }
    # endregion

    # region spicy
    #[Test]
    #[Group('crud')]
    /**
     * route('filament.ivpl.resources.filament.resources.products.process_selections').
     *
     *
     **/
    public function it_products_process_selections(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->marktestskipped('Skipped test.');
        // $this->authenticate();
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $payload = [
            'category_id' => $productCategory->id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $product1 = Product::factory()->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('processSelections', $product1);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * route('filament.ivpl.resources.filament.resources.products.process_selections').
     *
     **/
    public function it_fails_to_process_selections_without_product_ids(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->marktestskipped('Skipped test.');
        // $this->authenticate();
        $productCategory = ProductCategory::factory()->create([
            'category_name' => '::category_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $payload = [
            'category_id' => $productCategory->id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $product = Product::factory()->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->callAction('processSelections', $product);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();
    }
    # endregion
}
