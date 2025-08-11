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
        /* arrange */
        $company = Company::factory()->create(['name' => 'Acme LLC']);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('companies', $company->toArray());
    }
    # endregion

    # region modals
    #[Test]
    #[Group('modals')]
    public function it_creates_a_company_through_a_modal(): void
    {
        /* arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane LLC',
            'slug'        => 'invoiceplane_llc',
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

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "name": "InvoicePlane Corp"
     * }
     */
    public function it_fails_to_create_company_through_a_modal_without_required_search_code(): void
    {
        /* arrange */
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
        /* arrange */
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
        /* arrange */
        $company = Company::factory()->create([
            'search_code' => 'OLDCODE',
            'name'        => 'Old Name',
        ]);

        $updatedData = [
            'search_code' => 'NEWCODE',
            'name'        => 'Updated Corp',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction(TestAction::make('edit')->table($company), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('companies', array_merge(
            ['id' => $company->id],
            $updatedData
        ));
    }

    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "search_code": "IVPLV2",
     *   "name": "InvoicePlane Corp"
     * }
     */
    public function it_creates_a_company(): void
    {
        /* arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane LLC',
            'slug'        => 'invoiceplane_llc',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_when_search_code_missing(): void
    {
        /* arrange */
        $payload = ['name' => 'InvoicePlane Corp'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /*if (app()->runningUnitTests()) {
            dump($payload);
        }*/

        /* assert */
        $component
            ->assertHasFormErrors(['search_code']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_without_required_name(): void
    {
        /* arrange */
        $payload = [
            'search_code' => 'IVPLV2',
            'slug'        => 'slug_should_be_generated',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_company(): void
    {
        /* arrange */
        $company = Company::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'InvoicePlane Corp'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(EditCompany::class, ['record' => $company->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
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
        $this->markTestIncomplete();

        /* arrange */
        $company = Company::factory()->create([
            'search_code' => 'TODELETE',
            'name'        => 'Company to Delete',
        ]);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->callAction('delete', $company);

        /* assert */
        $component->assertSuccessful();
        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }
    # endregion

    #region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_companies_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a company with a different tenant
        $otherCompany = Company::factory()->create([
            'search_code' => 'OTHER',
            'name'        => 'Other Tenant',
        ]);

        // Try to access the other company's edit page
        $response = Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->mountAction(TestAction::make('edit')->table($task), $updatedData);

        // Should either be forbidden or not found
        $response->assertStatus(404);
    }
    # endregion

    #region spicy
    # endregion
}
