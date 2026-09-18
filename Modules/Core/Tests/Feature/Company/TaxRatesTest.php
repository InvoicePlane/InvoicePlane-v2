<?php

namespace Modules\Core\Tests\Feature\Company;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Company\Resources\TaxRates\Pages\CreateTaxRate;
use Modules\Core\Filament\Company\Resources\TaxRates\Pages\EditTaxRate;
use Modules\Core\Filament\Company\Resources\TaxRates\Pages\ListTaxRates;
use Modules\Core\Filament\Company\Resources\TaxRates\TaxRateResource;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TaxRateResource::class)]
class TaxRatesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_tax_rates(): void
    {
        /* Arrange */
        $taxRate = TaxRate::factory()->for($this->company)->create([
            'name' => 'Standard VAT',
            'code' => 'STDVAT',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class);

        /* Assert */
        $component->assertSuccessful();
        // 'name' column is truncated with ->limit(10) in TaxRatesTable, so
        // assert on the untruncated 'code' column instead.
        $component->assertSee('STDVAT');

        $this->assertDatabaseHas('tax_rates', ['id' => $taxRate->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_does_not_show_tax_rates_from_another_company(): void
    {
        /* Arrange */
        $other = TaxRate::factory()->for(Company::factory()->create())->create([
            'name' => 'Other Company VAT',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_creates_a_tax_rate_with_the_current_company_id(): void
    {
        /* Arrange */
        $payload = [
            'code'          => 'STD21',
            'name'          => 'Standard Rate',
            'tax_rate_type' => 'exclusive',
            'rate'          => 21.0,
            'is_active'     => true,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTaxRate::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', [
            'code'       => 'STD21',
            'name'       => 'Standard Rate',
            'company_id' => $this->company->id,
        ]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_a_tax_rate_without_required_code(): void
    {
        /* Arrange */
        $payload = [
            'name'          => 'Missing Code Tax',
            'tax_rate_type' => 'exclusive',
            'rate'          => 10.0,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTaxRate::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['code']);

        $this->assertDatabaseMissing('tax_rates', ['name' => 'Missing Code Tax']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_tax_rate(): void
    {
        /* Arrange */
        $taxRate = TaxRate::factory()->for($this->company)->create([
            'name' => 'Old Rate',
            'code' => 'OLD',
        ]);

        $payload = ['name' => 'Updated Rate'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditTaxRate::class, ['record' => $taxRate->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', array_merge($payload, [
            'id' => $taxRate->id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_tax_rate(): void
    {
        /* Arrange */
        $taxRate = TaxRate::factory()->for($this->company)->create([
            'name' => 'Rate to Delete',
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('delete')->table($taxRate))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('tax_rates', ['id' => $taxRate->id]);
    }
    # endregion
}
