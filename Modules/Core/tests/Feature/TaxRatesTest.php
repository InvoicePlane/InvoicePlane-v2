<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;

class TaxRatesTest extends AbstractTestCase
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
     */
    public function it_shows_tax_rates_index(): void
    {
        $user = User::factory()->create();

        TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '15',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.resources.tax-rates.index'));
        $response->assertStatus(200);
        $response->assertSee('::tax_rate_name::');
    }

    /**
     * @test
     */
    public function it_creates_a_tax_rate(): void
    {
        $user = User::factory()->create();

        $payload = [
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '15',
        ];

        $response = $this->actingAs(user: $user, guard: 'web')->post(route('filament.resources.tax-rates.store'), $payload);
        $response->assertRedirect(route('filament.resources.tax-rates.index'));

        $this->assertDatabaseHas('tax_rates', ['tax_rate_name' => '::tax_rate_name::']);
    }

    /**
     * @test
     */
    public function it_updates_a_tax_rate(): void
    {
        $user = User::factory()->create();

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::original_tax_rate_name::',
            'tax_rate_percent' => '15',
        ]);

        $payload = [
            'tax_rate_name'    => '::updated_tax_rate_name::',
            'tax_rate_percent' => '20',
        ];

        $response = $this->actingAs(user: $user, guard: 'web')->put(route('filament.resources.tax-rates.update', ['record' => $taxRate->tax_rate_id]), $payload);
        $response->assertRedirect(route('filament.resources.tax-rates.index'));

        $taxRate->refresh();
        $this->assertEquals('::updated_tax_rate_name::', $taxRate->tax_rate_name);
    }

    /**
     * @test
     */
    public function it_deletes_a_tax_rate(): void
    {
        $user = User::factory()->create();

        $taxRate = TaxRate::factory()->create();

        $response = $this->actingAs(user: $user, guard: 'web')->delete(route('filament.resources.tax-rates.destroy', ['record' => $taxRate->tax_rate_id]));
        $response->assertRedirect(route('filament.resources.tax-rates.index'));

        $this->assertDatabaseMissing('tax_rates', ['tax_rate_id' => $taxRate->tax_rate_id]);
    }
}
