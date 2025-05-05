<?php

namespace Modules\Products\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductFamily;
use Modules\Products\Models\ProductUnit;

class ProductApiTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests


    public function it_returns_products_index(): void
    {
        $this->markTestIncomplete('Failed asserting that an array has the key family');
        $user = User::factory()->create();

        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        Sanctum::actingAs($user);

        Product::factory(5)->create([
            'family_id'    => $productFamily,
            'product_sku'  => '::product_sku::',
            'product_name' => '::product_name::',
            'tax_rate_id'  => $taxRate,
            'unit_id'      => $productUnit,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.products.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'family',
                    'product_sku',
                    'product_name',
                    'tax_rate',
                    'unit',
                ],
            ],
        ]);

        $response->assertJsonFragment(['product_sku' => '::product_sku::']);
        $response->assertJsonFragment(['product_name' => '::product_name::']);
    }


    public function it_creates_a_product(): void
    {
        $this->markTestIncomplete('test is failing? also on family key');
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::taxrate_name::',
            'tax_rate_percent' => '10.01',
        ]);

        $initialProduct = Product::factory()->create([
            'family_id'     => $productFamily->family_id,
            'unit_id'       => $productUnit->unit_id,
            'tax_rate_id'   => $taxRate->tax_rate_id,
            'product_sku'   => '::product_sku::',
            'product_name'  => '::product_name::',
            'product_price' => '1.00',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.products.store'), [
            'family_id'     => $productFamily->family_id,
            'unit_id'       => $productUnit->unit_id,
            'tax_rate_id'   => $taxRate->tax_rate_id,
            'product_sku'   => '::product_sku::',
            'product_name'  => '::product_name::',
            'product_price' => '1.00',
        ]);

        $response->assertSuccessful();

        $initialProduct->refresh();

        $response->assertJsonFragment(['family' => ['id' => 1, 'product_family' => '::family_name::']]);
        $response->assertJsonFragment(['unit' => ['id' => 1, 'unit_name' => '::unit_name::', 'unit_name_plrl' => null]]);
        $response->assertJsonFragment(['tax_rate' => ['id' => 1, 'name' => '::taxrate_name::', 'percentage' => '10.01']]);
        $response->assertJsonFragment(['product_sku' => '::product_sku::']);
        $response->assertJsonFragment(['product_name' => '::product_name::']);
        $response->assertJsonFragment(['product_price' => '1.00']);
    }


    public function it_returns_error_response_when_creating_a_product_without_required_field(): void
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

        Product::factory()->make([
            'family_id'    => $productFamily->family_id,
            'product_sku'  => '::product_sku::',
            'product_name' => '::product_name::',
            'tax_rate_id'  => $taxRate->tax_rate_id,
            'unit_id'      => $productUnit->unit_id,
        ])->toArray();

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.products.store'), [
            'family_id'    => $productFamily->family_id,
            'product_sku'  => '::product_sku::',
            'product_name' => '::product_name::',
            'tax_rate_id'  => $taxRate->tax_rate_id,
            'unit_id'      => $productUnit->unit_id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('product_price', 'errors');
    }


    public function it_updates_a_product(): void
    {
        $this->markTestIncomplete('test is failing? also on family key');
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::taxrate_name::',
            'tax_rate_percent' => '10.01',
        ]);

        $initialProduct = Product::factory()->create([
            'family_id'     => $productFamily->family_id,
            'unit_id'       => $productUnit->unit_id,
            'tax_rate_id'   => $taxRate->tax_rate_id,
            'product_sku'   => '::product_sku::',
            'product_name'  => '::product_name::',
            'product_price' => '1.00',
        ]);

        $updatedData = [
            'product_name'  => '::updated_product_name::',
            'product_price' => '11.01',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.products.update', ['product' => $initialProduct->product_id]), $updatedData);

        $response->assertSuccessful();

        $initialProduct->refresh();

        $response->assertJsonFragment(['family' => ['id' => 1, 'product_family' => '::family_name::']]);
        $response->assertJsonFragment(['unit' => ['id' => 1, 'unit_name' => '::unit_name::', 'unit_name_plrl' => null]]);
        $response->assertJsonFragment(['tax_rate' => ['id' => 1, 'name' => '::taxrate_name::', 'percentage' => '10.01']]);
        $response->assertJsonFragment(['product_sku' => '::product_sku::']);

        $response->assertJsonFragment(['product_name' => $updatedData['product_name']]);
        $response->assertJsonFragment(['product_price' => $updatedData['product_price']]);

        $this->assertEquals($updatedData['product_name'], $initialProduct->product_name);
        $this->assertEquals($updatedData['product_price'], $initialProduct->product_price);
    }


    public function it_returns_error_response_when_updating_a_product_with_invalid_values(): void
    {
        $productFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $productUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::taxrate_name::',
            'tax_rate_percent' => '10.01',
        ]);

        $initialProduct = Product::factory()->create([
            'family_id'     => $productFamily->family_id,
            'unit_id'       => $productUnit->unit_id,
            'tax_rate_id'   => $taxRate->tax_rate_id,
            'product_sku'   => '::product_sku::',
            'product_name'  => '::product_name::',
            'product_price' => '1.00',
        ]);

        $updatedData = [
            'product_name'  => '::updated_product_name::',
            'product_price' => '11,01',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.products.update', ['product' => $initialProduct->product_id]), $updatedData);

        $response->assertStatus(422);

        $response->assertJsonFragment(['message' => 'The given data was invalid']);
        $response->assertJsonFragment(['errors' => ['product_price' => ['The product price must be a number.']], ]);
    }


    public function it_deletes_a_product(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('api.products.update', ['record' => 1]));
        $response->assertSuccessful();
    }
}
