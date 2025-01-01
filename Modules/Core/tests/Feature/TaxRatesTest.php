<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\TaxRateResource\Pages\CreateTaxRate;
use Modules\Core\Filament\Resources\TaxRateResource\Pages\EditTaxRate;
use Modules\Core\Filament\Resources\TaxRateResource\Pages\ManageTaxRates;
use Modules\Core\Models\TaxRate;
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
     *
     * @skip Not implemented yet
     */
    public function it_shows_tax_rates_index(): void
    {
        // $this->authenticate();

        TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '15',
        ]);

        Livewire::test(ManageTaxRates::class)
            ->assertStatus(200)
            ->assertSee('::tax_rate_name::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_tax_rate(): void
    {
        // $this->authenticate();

        $payload = [
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '15',
        ];

        Livewire::test(CreateTaxRate::class)
            ->set('data.tax_rate_name', $payload['tax_rate_name'])
            ->set('data.tax_rate_percent', $payload['tax_rate_percent'])
            ->call('create')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tax_rates', $payload);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_tax_rate(): void
    {
        // $this->authenticate();

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::original_tax_rate_name::',
            'tax_rate_percent' => '15',
        ]);

        $updatedData = [
            'tax_rate_name'    => '::updated_tax_rate_name::',
            'tax_rate_percent' => '20',
        ];

        Livewire::test(EditTaxRate::class, ['record' => $taxRate->tax_rate_id])
            ->set('data.tax_rate_name', $updatedData['tax_rate_name'])
            ->set('data.tax_rate_percent', $updatedData['tax_rate_percent'])
            ->call('save')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tax_rates', array_merge($updatedData, [
            'tax_rate_id' => $taxRate->tax_rate_id,
        ]));
    }

    /**
     * @test
     *
     * @incomplete Not implemented yet
     */
    public function it_deletes_a_tax_rate(): void
    {
        $this->markTestIncomplete('Needs delete table action');
        // $this->authenticate();
        $taxRate = TaxRate::factory()->create();

        Livewire::test(ManageTaxRates::class)
            ->callTableAction('delete', $taxRate->tax_rate_id)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('tax_rates', ['tax_rate_id' => $taxRate->tax_rate_id]);
    }
    // endregion
}
