<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\TaxRateResource;
use Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages\CreateTaxRate;
use Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages\EditTaxRate;
use Modules\Core\Filament\Admin\Resources\TaxRateResource\Pages\ListTaxRates;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TaxRateResource::class)]

class TaxRatesTest extends AbstractTestCase
{
    use RefreshDatabase;
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
    #[Group('crud')]
    public function it_lists_tax_rates(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Some error with a livewire view');

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'    => 'Value',
            'tax_rate_type' => 'Value',
            'is_active'     => true,
            'name'          => 'Example',
            'code'          => 'Example',
            'rate'          => 'Example',
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListTaxRates::class);

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', $payload);
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\TaxRateResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "tax_rate_type": "Value",
     * "is_active": "true",
     * "name": "Example",
     * "code": "Example",
     * "rate": "Example"
     * }
     */
    #[Group('crud')]
    public function it_creates_a_taxrate(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Some error with a livewire view');

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'    => 'Value',
            'tax_rate_type' => 'Value',
            'is_active'     => true,
            'name'          => 'Example',
            'code'          => 'Example',
            'rate'          => 'Example',
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateTaxRate::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\TaxRateResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "tax_rate_type": "Value",
     * "is_active": "true",
     * "name": "Example",
     * "code": "Example",
     * "rate": "Example"
     * }
     */
    #[Group('crud')]
    public function it_updates_a_taxrate(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = TaxRate::factory()->create();

        $payload = [
            'company_id'    => 'Value',
            'tax_rate_type' => 'Value',
            'is_active'     => true,
            'name'          => 'Example',
            'code'          => 'Example',
            'rate'          => 'Example',
        ];

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name'    => '::original_tax_rate_name::',
            'tax_rate_percent' => '15',
        ]);

        $updatedData = [
            'tax_rate_name'    => '::updated_tax_rate_name::',
            'tax_rate_percent' => '20',
        ];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditTaxRate::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', array_merge($updatedData, [
            'tax_rate_id' => $taxRate->tax_rate_id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "tax_rate_type": "Value",
     * "is_active": "true",
     * "name": "Example",
     * "code": "Example",
     * "rate": "Example"
     * }
     */
    #[Group('crud')]
    public function it_deletes_a_taxrate(): void
    {
        $this->markTestIncomplete('Needs delete table action, confirmation logic, failing tests');

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $record = TaxRate::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListTaxRates::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('tax_rates', ['id' => $record->id]);
    }

    // endregion

    // region usp
    // endregion
}
