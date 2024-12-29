<?php

namespace Modules\Products\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;
use Modules\Products\Models\ProductFamily;

class ProductFamiliesApiTest extends AbstractTestCase
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

    /**
     * @test
     */
    public function it_returns_product_families_index(): void
    {
        Sanctum::actingAs(User::factory()->create());

        ProductFamily::factory(5)->create([
            'family_name' => '::family_name::',
        ]);

        $response = $this->get(route('api.product_families.index'));
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'product_family',
                ],
            ],
        ]);

        $response->assertJsonFragment(['product_family' => '::family_name::']);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_lists_product_families_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->getJson(route('api.productfamilies.index'));
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_creates_a_product_family(): void
    {
        $initialFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $response = $this->post(route('api.product_families.store'), [
            'family_name' => $initialFamily->family_name,
        ]);

        $response->assertStatus(201);

        $initialFamily->refresh();
        $response->assertJsonFragment(['product_family' => '::family_name::']);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for creating a product family:
     *
     *
     *            [
     *            'family_name' => 'Test Family',
     *            ]
     */
    public function it_creates_a_product_family_via_api(): void
    {
        $payload = [
            'family_name' => 'Test Family',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.productfamilies.create'), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     */
    public function it_returns_error_response_with_invalid_family_name_key(): void
    {
        $initialFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);

        $response = $this->post(route('api.product_families.store'), [
            'family_naame' => $initialFamily->family_name,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('family_name', 'errors');
    }

    /**
     * @test
     */
    public function it_updates_a_product_family(): void
    {
        $initialProductFamily = ProductFamily::factory()->create([
            'family_name' => '::family_name::',
        ]);
        $updatedData = [
            'family_name' => '::updated_family_name::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.product_families.update', ['productFamily' => $initialProductFamily->family_id]), $updatedData);

        $response->assertStatus(200);
        $initialProductFamily->refresh();

        $response->assertJsonFragment(['product_family' => $updatedData['family_name']]);

        $this->assertEquals($updatedData['family_name'], $initialProductFamily->family_name);
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
    public function it_updates_a_product_family_via_api(): void
    {
        $payload = [
            'family_name' => 'Updated Family',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('api.productfamilies.update', ['productfamily' => 1]), $payload);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_delete_family(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $family = ProductFamily::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/families/' . $family->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/families/' . $family->id
        );

        $this->response->assertStatus(404);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_product_family_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('api.productfamilies.delete', ['productfamily' => 1]));
        $response->assertStatus(200);
    }
}
