<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\CreateTaxRate;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\EditTaxRate;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\ListTaxRates;
use Modules\Core\Filament\Admin\Resources\TaxRates\TaxRateResource;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TaxRateResource::class)]
class TaxRatesTest extends AbstractAdminPanelTestCase
{
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

    # region smoke
    #[Test]
    #[Group('crud')]
    public function it_lists_tax_rates(): void
    {
        /* arrange */
        $taxRate = TaxRate::factory()->create([
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'name'          => 'Example Tax',
            'code'          => 'EX',
            'rate'          => 15.00,
        ]);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class);

        /* assert */
        $component->assertSuccessful();

        // Optional: direct DB check
        $this->assertDatabaseHas('tax_rates', [
            'name' => $taxRate->name,
            'code' => $taxRate->code,
            'rate' => $taxRate->rate,
        ]);
    }
    # endregion

    # region crud
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

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateTaxRate::class)->fillForm($payload)->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

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

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditTaxRate::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

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

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListTaxRates::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('tax_rates', ['id' => $record->id]);
    }

    # endregion

    # region spicy
    # endregion
}
