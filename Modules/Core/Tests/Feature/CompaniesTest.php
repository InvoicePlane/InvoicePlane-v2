<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\CreateCompany;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\EditCompany;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListCompanies::class)]
class CompaniesTest extends AbstractAdminPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Acme LLC']
     */
    #[Group('crud')]
    public function it_lists_companies(): void
    {
        /* Arrange */
        $company = Company::factory()->create(['name' => 'Acme LLC']);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class);

        /* Assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('companies', $company->toArray());
    }
    # endregion

    # region modals
    #[Test]
    #[Group('modals')]
    public function it_creates_a_company_through_a_modal(): void
    {
        /* Arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane LLC',
            'slug'        => 'invoiceplane_llc',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "name": "InvoicePlane Corp"
     * }
     */
    public function it_fails_to_create_company_through_a_modal_without_required_search_code(): void
    {
        /* Arrange */
        $payload = ['name' => 'InvoicePlane Corp'];

        /* act & assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['search_code' => 'required']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "search_code": "IVPLV2",
     *   "slug": "slug_should_be_generated"
     * }
     */
    public function it_fails_to_create_company_through_a_modal_without_required_name(): void
    {
        /* Arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'slug'        => 'slug_should_be_generated',
        ];

        /* act & assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "name": "Updated Corp"
     * }
     */
    public function it_updates_a_company_through_a_modal(): void
    {
        /* Arrange */
        $company = Company::factory()->create([
            'search_code' => 'OLDCODE',
            'name'        => 'Old Name',
        ]);

        $updatedData = [
            'search_code' => 'NEWCODE',
            'name'        => 'Updated Corp',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction(TestAction::make('edit')->table($company), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('companies', array_merge(
            ['id' => $company->id],
            $updatedData
        ));
    }
    # endregion

    # region modals
    #[Test]
    #[Group('modals')]
    public function it_creates_a_company_trough_a_modal(): void
    {
        $this->markTestIncomplete('need revisit, slug not generated');
        /* arrange */
        $payload = [
            'search_code' => 'ROCKETCORP',
            'name'        => 'Acme LLC',
            'slug'        => 'acme-llc',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertSuccessful();
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('companies', $payload);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_a_company(): void
    {
        /* Arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane LLC',
            'slug'        => 'invoiceplane_llc',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_when_search_code_missing(): void
    {
        /* Arrange */
        $payload = ['name' => 'InvoicePlane Corp'];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertHasFormErrors(['search_code']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_without_required_name(): void
    {
        /* Arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'slug'        => 'slug_should_be_generated',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['name']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_company(): void
    {
        /* Arrange */
        $company = Company::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'InvoicePlane Corp'];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditCompany::class, ['record' => $company->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "id": "<id>"
     * }
     */
    public function it_deletes_a_company(): void
    {
        $this->markTestIncomplete('do not delete companies yet');

        /* Arrange */
        $company = Company::factory()->create([
            'search_code' => 'TODELETE',
            'name'        => 'Company to Delete',
        ]);
        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListCompanies::class)
            ->mountAction(TestAction::make('delete')->table($company))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
    # endregion

    #region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
