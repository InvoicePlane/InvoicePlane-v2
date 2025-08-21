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
        /* arrange */
        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'code'          => 'EXCL21',
            'name'          => '::taxrate_name::',
            'rate'          => 21.0000,
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
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

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('edit')->table($record), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
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
        /* arrange */
        $payload = [
            'tax_rate_type' => TaxRateType::EXCLUSIVE,
            'is_active'     => true,
            'code'          => 'EXCL21',
            'name'          => '::taxrate_name::',
            'rate'          => 21.0000,
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateTaxRate::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
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
    public function it_updates_a_taxrate(): void
    {
        /* arrange */
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

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditTaxRate::class, ['record' => $taxRate->getKey()])
            ->fillForm($updatedData)
            ->call('save');

        /* assert */
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
    public function it_deletes_a_taxrate(): void
    {
        /* arrange */
        $record = TaxRate::factory()->create([
            'name'          => 'Tax to Delete',
            'code'          => 'DELETEME',
            'rate'          => 10.0,
            'tax_rate_type' => 'percentage',
        ]);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->callAction('delete', $record);

        /* assert */
        $component->assertSuccessful();
        $this->assertSoftDeleted('tax_rates', ['id' => $record->id]);
    }

    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_tax_rates_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a tax rate with a different tenant
        $otherTaxRate = TaxRate::factory()->create([
            'name' => 'Other Tenant Tax',
            'code' => 'OTHER',
            'rate' => 15.0,
        ]);

        // Try to access the other tenant's tax rate
        $response = Livewire::actingAs($this->superAdmin())
            ->test(ListTaxRates::class)
            ->mountAction(TestAction::make('edit')->table($task), $updatedData);

        // Should be forbidden or not found
        $response->assertStatus(404);
    }
    # endregion

    # region spicy
    # endregion
}
