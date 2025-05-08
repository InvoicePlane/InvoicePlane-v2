<?php

namespace Modules\Core\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
// use Laravel\Sanctum\Sanctum;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;

class TaxRatesApiTest extends AbstractTestCase
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

    public function it_returns_tax_rates_index(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        TaxRate::factory(5)->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => rand(6, 21),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.tax_rates.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'percentage',
                ],
            ],
        ]);
        $response->assertJsonFragment(['name' => '::tax_rate_name::']);
    }

    public function it_creates_a_tax_rate(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $initialTaxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '21',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.tax_rates.store'), [
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '21',
        ]);

        $response->assertSuccessful();

        $initialTaxRate->refresh();

        $response->assertJsonFragment(['name' => '::tax_rate_name::']);
        $response->assertJsonFragment(['percentage' => '21.00']);
    }

    public function it_returns_error_response_when_creating_a_tax_rate_with_wrong_fields(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $client = Relation::factory()->create([
            'client_name' => '::client_name::',
        ]);
        $initialTaxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '21',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.tax_rates.store'), [
            'client_id'    => $client->client_id,
            'taxRate_name' => '::taxRate_name::',
        ]);

        $response->assertStatus(422);

        $response->assertJsonFragment(['message' => 'The given data was invalid']);
        $response->assertJsonValidationErrorFor('tax_rate_name', 'errors');
    }

    public function it_updates_a_tax_rate(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $initialTaxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '21',
        ]);

        $updatedData = [
            'tax_rate_name' => '::updated_tax_rate_name::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.tax_rates.update', ['taxRate' => $initialTaxRate->tax_rate_id]), $updatedData);

        $response->assertSuccessful();

        $initialTaxRate->refresh();

        $response->assertJsonFragment(['name' => $updatedData['tax_rate_name']]);
    }

    public function it_deletes_a_tax_rate(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $initialTaxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '21',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(
            route('api.tax_rates.destroy', ['taxRate' => $initialTaxRate->tax_rate_id])
        );

        $response->assertSuccessful();

        $getTaxRateResponse = $this->getJson(
            route(
                'api.tax_rates.show',
                ['taxRate' => $initialTaxRate->tax_rate_id]
            )
        );
        $getTaxRateResponse->assertNotFound();
    }
}
