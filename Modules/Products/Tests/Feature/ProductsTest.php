<?php

namespace Modules\Products\Tests\Feature;

use Filament\Actions\Testing\TestAction;
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
            'tax_rate_2_id'  => null,
            'description'    => 'Example',
        ];
        $product = Product::factory()->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class, ['tenant' => Str::lower($this->company->search_code)]);

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
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dd($payload);
        }*/

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
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

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
    public function it_fails_to_create_product_through_a_modal_without_required_product_name(): void
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
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['product_name']);
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
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['price']);

        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product_through_a_modal(): void
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

        $product = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'type'          => ProductType::PRODUCT->value,
            'code'          => 'SKU-001',
            'product_name'  => 'Test Product',
            'price'         => 9.99,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
            'description'   => 'Example',
        ]);

        $payload = [
            'product_name' => 'Updated Product',
            'price'        => 70.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction(TestAction::make('edit')->table($product), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        $component
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', array_merge($payload, [
            'id' => $product->id,
        ]));
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

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

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

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

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
    public function it_fails_to_create_product_without_required_product_name(): void
    {
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

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['product_name']);
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

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['price']);

        $this->assertDatabaseMissing('products', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_product(): void
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

        $product = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'type'          => ProductType::PRODUCT->value,
            'code'          => 'SKU-001',
            'product_name'  => 'Test Product',
            'price'         => 9.99,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
            'description'   => 'Example',
        ]);

        $payload = [
            'product_name' => 'Updated Product',
            'price'        => 70.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditProduct::class, ['record' => $product->id])
            ->fillForm($payload)
            ->call('save');

        $this->assertDatabaseHas('products', array_merge($payload, [
            'id' => $product->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_product(): void
    {
        /* arrange */
        $productCategory = ProductCategory::factory()->for($this->company)->create([
            'category_name' => '::category_name::',
        ]);
        $productUnit = ProductUnit::factory()->for($this->company)->create([
            'unit_name' => '::unit_name::',
        ]);
        $taxRate = TaxRate::factory()->for($this->company)->create([
            'name' => '::taxrate_name::',
        ]);

        $product = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'type'          => ProductType::PRODUCT->value,
            'code'          => 'SKU-001',
            'product_name'  => 'Test Product',
            'price'         => 9.99,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
            'description'   => 'Example',
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction(TestAction::make('delete')->table($product))
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_bulk_deletes_products(): void
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
            'category_id' => $productCategory->id,
            'tax_rate_id' => $taxRate->tax_rate_id,
            'unit_id'     => $productUnit->unit_id,
        ];

        $products = Product::factory(3)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListProducts::class)
            ->mountAction(TestAction::make('bulkDelete')->table($product))
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        foreach ($products as $product) {
            $this->assertDatabaseMissing('products', [
                'id' => $product->id,
            ]);
        }
    }
    # endregion

    # region multi-tenancy
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
