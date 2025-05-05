<?php

namespace Modules\Core\Tests\Feature;

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
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
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
    public function it_creates_a_taxrate(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'    => 'Value',
            'tax_rate_type' => 'Value',
            'is_active'     => true,
            'name'          => 'Example',
            'code'          => 'Example',
            'rate'          => 'Example',
        ];

        Livewire::test(CreateTaxRate::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
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
    public function it_updates_a_taxrate(): void
    {
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

        Livewire::test(EditTaxRate::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
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
    public function it_deletes_a_taxrate(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = TaxRate::factory()->create();

        Livewire::test(ListTaxRates::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('taxrates', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
