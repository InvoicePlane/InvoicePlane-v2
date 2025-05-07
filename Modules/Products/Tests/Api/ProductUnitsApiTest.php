<?php

namespace Modules\Products\Tests\Api;

use Modules\Products\Models\ProductUnit;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Products\Tests\Api\ProductUnitsApiTest;

use Modules\Core\Models\User;

use Modules\Core\Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;

class ProductUnitsApiTest extends AbstractTestCase
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

    // region CRUD tests

    public function it_returns_product_units_index(): void
    {
        Sanctum::actingAs(User::factory()->create());

        ProductUnit::factory(5)->create([
            'unit_name' => '::unit_name::',
        ]);
        $response = $this->get(route('api.product_units.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'unit_name',
                ],
            ],
        ]);
        $response->assertJsonFragment(['unit_name' => '::unit_name::']);
    }

    public function it_lists_product_units_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->getJson(route('api.filament.ivpl.resources.filament.resources.productunits.index'));
        $response->assertSuccessful();
    }

    public function it_creates_a_product_unit(): void
    {
        $initialProductUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $response = $this->post(route('api.product_units.store'), [
            'unit_name' => $initialProductUnit->unit_name,
        ]);

        $response->assertSuccessful();

        $initialProductUnit->refresh();
        $response->assertJsonFragment(['unit_name' => '::unit_name::']);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for creating a product unit:
     *
     *
     *            [
     *            'unit_name' => 'Test Unit',
     *            'unit_symbol' => 'TU',
     *            ]
     */
    public function it_creates_a_product_unit_via_api(): void
    {
        $payload = [
            'unit_name'   => 'Test Unit',
            'unit_symbol' => 'TU',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.filament.ivpl.resources.productunits.store'), $payload);
        $response->assertSuccessful();
    }

    public function it_returns_error_response_with_invalid_or_missing_unit_name_value(): void
    {
        $initialProductUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);

        $response = $this->post(route('api.product_units.store'), [
            'unit_name' => $initialProductUnit->unit_name,
        ]);

        $response->assertSuccessful();

        $initialProductUnit->refresh();
        $response->assertJsonFragment(['unit_name' => '::unit_name::']);
    }

    public function it_updates_a_product_unit(): void
    {
        $initialProductUnit = ProductUnit::factory()->create([
            'unit_name' => '::unit_name::',
        ]);
        $updatedData = [
            'unit_name' => '::updated_unit_name::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.product_units.update', ['productUnit' => $initialProductUnit->unit_id]), $updatedData);

        $response->assertSuccessful();
        $initialProductUnit->refresh();

        $response->assertJsonFragment(['unit_name' => $updatedData['unit_name']]);

        $this->assertEquals($updatedData['unit_name'], $initialProductUnit->unit_name);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for updating a product unit:
     *
     *
     *            [
     *            'unit_name' => 'Updated Unit',
     *            'unit_symbol' => 'UU',
     *            ]
     */
    public function it_updates_a_product_unit_via_api(): void
    {
        $payload = [
            'unit_name'   => 'Updated Unit',
            'unit_symbol' => 'UU',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('api.filament.ivpl.resources.filament.resources.productunits.update', ['record' => 1]), $payload);
        $response->assertSuccessful();
    }

    public function it_delete_unit(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $unit = ProductUnit::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/units/' . $unit->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/units/' . $unit->id
        );

        $this->response->assertStatus(404);
    }

    public function it_deletes_a_product_unit_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('api.filament.ivpl.resources.filament.resources.productunits.delete', ['record' => 1]));
        $response->assertSuccessful();
    }
}
