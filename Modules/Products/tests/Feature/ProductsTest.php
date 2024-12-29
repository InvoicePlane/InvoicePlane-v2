<?php

namespace Modules\Products\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\tests\AbstractTestCase;
use Modules\Products\Models\Product;

class ProductsTest extends AbstractTestCase
{
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

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_lists_products(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->getJson(route('products.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function it_products_create(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.products.store'), [
            'product_name'  => $product->product_name,
            'product_price' => 50.00,
            'category_id'   => $product->category_id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'product_name' => $product->product_name,
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_product(): void
    {
        // Payload for creating a product
        // @var array $payload
        $payload = [
            'family_id'           => 1, // Replace with a valid ProductFamily ID
            'product_sku'         => 'TESTSKU',
            'product_name'        => 'Test Product',
            'product_description' => 'A test description for the product.',
            'product_price'       => 25.50,
            'purchase_price'      => 15.00,
            'provider_name'       => 'Test Provider',
            'tax_rate_id'         => 1, // Replace with a valid TaxRate ID
            'unit_id'             => 1, // Replace with a valid ProductUnit ID
            'product_tariff'      => 12345,
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('filament.ivpl.resources.products.store'), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_product(): void
    {
        // Payload for updating a product
        // @var array $payload
        $payload = [
            'product_name'        => 'Updated Product Name',
            'product_description' => 'Updated product description.',
            'product_price'       => 30.00,
            'purchase_price'      => 20.00,
            'product_tariff'      => 67890,
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('products.update', ['product' => 1]), $payload);
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_product(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('products.delete', ['product' => 1]));
        $response->assertStatus(200);
    }
    // endregion

    // region Spicy Tests

    /** @test */
    public function it_products_process_selections(): void
    {
        // $this->authenticate();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.products.process_selections'), [
            'product_ids' => [$product1->product_id, $product2->product_id],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_fails_to_process_selections_without_product_ids(): void
    {
        // $this->authenticate();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.products.process_selections'));

        $response->assertStatus(422);
    }
}
