<?php

namespace Modules\Core\Tests\Feature;

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

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_when_search_code_exceeds_max_length(): void
    {
        /* Arrange — regression guard: companies.search_code is varchar(10);
         * without ->maxLength(10) on the form field, a longer value passed
         * client validation and blew up as an unhandled SQL truncation 500
         * instead of a form validation message. */
        $payload = [
            'search_code' => 'ELEVENCHARS', // 11 chars — exceeds the varchar(10) column
            'name'        => 'InvoicePlane Corp',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['search_code']);
        $this->assertDatabaseMissing('companies', ['name' => $payload['name']]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_with_a_duplicate_name(): void
    {
        /* Arrange — regression guard: companies.name also has a unique DB
         * constraint (like search_code); without ->unique() on the form
         * field, a duplicate name hit an unhandled SQL 500 instead of a
         * validation message. */
        Company::factory()->create(['name' => 'Duplicate Corp']);

        $payload = [
            'search_code' => 'DUPNAME1',
            'name'        => 'Duplicate Corp',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['name']);
        $this->assertDatabaseMissing('companies', ['search_code' => $payload['search_code']]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_with_a_duplicate_search_code(): void
    {
        /* Arrange — regression guard: companies.search_code has a unique DB
         * constraint; without ->unique() on the form field, a duplicate hit
         * the same "unhandled 500 instead of a validation message" failure
         * mode as the length issue above. */
        Company::factory()->create(['search_code' => 'DUPCODE']);

        $payload = [
            'search_code' => 'DUPCODE',
            'name'        => 'Another Company',
        ];

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['search_code']);
        $this->assertDatabaseMissing('companies', ['name' => $payload['name']]);
    }
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
    #[Group('failing')]
    public function it_deletes_a_company(): void
    {
        $this->markTestSkipped('Company deletion intentionally not implemented yet');
    }
    # endregion

    #region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
