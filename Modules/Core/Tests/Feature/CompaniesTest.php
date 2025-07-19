<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\CompanyResource;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\CreateCompany;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\EditCompany;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CompanyResource::class)]
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
        $this->markTestIncomplete('need revisit, slug not generated');

        /* arrange */
        $payload = [
            'search_code' => 'ROCKETCORP',
            'name'        => 'Rocket Corp',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->runningUnitTests()) {
            dump($payload);
        }

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
        $payload = ['name' => 'Rocket Corp'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        if (app()->runningUnitTests()) {
            dump($payload);
        }

        /* assert */
        $component
            ->assertHasFormErrors(['search_code']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_when_name_missing(): void
    {
        /* arrange */
        $payload = [
            'search_code' => 'ROCKETCORP',
            'slug'        => 'slug_should_be_generated',
        ];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateCompany::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);

        $this->assertDatabaseMissing('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_company(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'Updated Corp'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditCompany::class, ['record' => $company->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_company(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListCompanies::class)->callTableAction('delete', $company);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
    # endregion

    # region multi-tenancy
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
            ->mountAction('edit', ['record' => $otherCompany->id]);

        // Should either be forbidden or not found
        $response->assertStatus(404);
    }
    # endregion
}
