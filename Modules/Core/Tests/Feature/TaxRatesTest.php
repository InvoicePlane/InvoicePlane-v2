<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\CreateTaxRate;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\EditTaxRate;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\ListTaxRates;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListTaxRates::class)]
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
    #[Group('smoke')]
    public function it_lists_tax_rates(): void
    {
        /* Arrange */
        $taxRate = TaxRate::factory()->create([
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'name'          => 'Example Tax',
            'code'          => 'EX',
            'rate'          => 15.00,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class);

        /* Assert */
        $component->assertSuccessful();

        // Optional: direct DB check
        $this->assertDatabaseHas('tax_rates', [
            'name' => $taxRate->name,
            'code' => $taxRate->code,
            'rate' => $taxRate->rate,
        ]);
    }
    # endregion

    # region modals
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
    public function it_creates_a_taxrate_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'code'          => 'EXCL21',
            'name'          => '::taxrate_name::',
            'rate'          => 21.0000,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tax_rates', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_a_taxrate_through_a_modal_with_a_duplicate_code(): void
    {
        /* Arrange — regression guard: tax_rates has a unique DB constraint
         * on (company_id, code); without ->unique() on the form field, a
         * duplicate code hit an unhandled SQL 500 instead of a validation
         * message, the same failure mode as the missing ->required() below. */
        TaxRate::factory()->for($this->company)->create(['code' => 'DUPTAX']);

        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'name'          => 'Duplicate Code Rate',
            'code'          => 'DUPTAX',
            'rate'          => 8.0,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['code']);
        $this->assertDatabaseMissing('tax_rates', ['name' => $payload['name']]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_a_taxrate_through_a_modal_without_required_code(): void
    {
        /* Arrange — regression guard: tax_rates.code is NOT NULL with no DB
         * default; without ->required() on the form field (it had no
         * asterisk either), a blank code passed client validation and blew
         * up as an unhandled SQLSTATE 500 on every submission. */
        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'name'          => 'No Code Rate',
            'rate'          => 5.0,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['code' => 'required']);
        $this->assertDatabaseMissing('tax_rates', ['name' => $payload['name']]);
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
    public function it_updates_a_taxrate_through_a_modal(): void
    {
        $record = TaxRate::factory()->create([
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'code'          => 'EXCL21',
            'name'          => '::taxrate_name::',
            'rate'          => 21.0000,
        ]);

        $updatedData = [
            'name' => 'Updated VAT Rate',
            'rate' => 22.0,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('edit')->table($record), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('tax_rates', array_merge(
            ['id' => $record->id],
            $updatedData
        ));
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * TaxRateResource.
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
        /* Arrange */
        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'code'          => 'EXCL21',
            'name'          => '::taxrate_name::',
            'rate'          => 21.0000,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateTaxRate::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tax_rates', $payload);
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
    public function it_updates_a_taxrate(): void
    {
        /* Arrange */
        $taxRate = TaxRate::factory()->create([
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'code'          => 'EXCL21',
            'name'          => '::taxrate_name::',
            'rate'          => 21.0000,
        ]);

        $updatedData = [
            'name' => '::updated_tax_rate_name::',
            'rate' => 21.0000,
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditTaxRate::class, ['record' => $taxRate->getKey()])
            ->fillForm($updatedData)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tax_rates', array_merge($updatedData, [
            'id' => $taxRate->id,
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
        /* Arrange */
        $taxRate = TaxRate::factory()->create([
            'name'          => 'Tax to Delete',
            'code'          => 'DELETEME',
            'rate'          => 10.0,
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('delete')->table($taxRate))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('tax_rates', ['id' => $taxRate->id]);
    }

    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    # endregion
}
