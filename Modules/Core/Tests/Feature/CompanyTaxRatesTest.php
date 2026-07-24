<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Filament\Company\Resources\TaxRates\Pages\ListTaxRates;
use Modules\Core\Filament\Company\Resources\TaxRates\TaxRateResource;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TaxRateResource::class)]
class CompanyTaxRatesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_tax_rates(): void
    {
        /* Arrange */
        $rate = TaxRate::factory()->for($this->company)->create(['name' => 'BE VAT']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee('BE VAT');

        $this->assertDatabaseHas('tax_rates', ['id' => $rate->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_does_not_show_tax_rates_from_another_company(): void
    {
        /* Arrange */
        $other = TaxRate::factory()->for(Company::factory()->create())->create(['name' => 'Other Co VAT']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertDontSee('Other Co VAT');
        $component->assertCanNotSeeTableRecords([$other]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_a_tax_rate_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'name'          => 'New Regional Tax',
            'code'          => 'REG01',
            'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
            'rate'          => 15.5,
            'is_active'     => true,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', [
            'name'       => 'New Regional Tax',
            'company_id' => $this->company->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_a_tax_rate_without_required_name(): void
    {
        /* Arrange */
        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
            'rate'          => 10,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_tax_rate_through_a_modal(): void
    {
        /* Arrange */
        $rate    = TaxRate::factory()->for($this->company)->create(['name' => 'Old Rate']);
        $payload = ['name' => 'Updated Rate'];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('edit')->table($rate), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tax_rates', [
            'id'   => $rate->id,
            'name' => 'Updated Rate',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_tax_rate(): void
    {
        /* Arrange */
        $rate = TaxRate::factory()->for($this->company)->create(['name' => 'To Delete']);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('delete')->table($rate))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('tax_rates', ['id' => $rate->id]);
    }
    # endregion
}
