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

    #[Test]
    #[Group('crud')]
    public function it_creates_a_company(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['name' => 'Rocket Corp'];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_company_when_name_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [];

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateCompany::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['name']);
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
}
