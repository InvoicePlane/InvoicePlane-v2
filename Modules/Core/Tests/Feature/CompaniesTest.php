<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\CompanyResource;
use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\CreateCompany;
use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\EditCompany;
use Modules\Core\Filament\Admin\Resources\CompanyResource\Pages\ListCompanies;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CompanyResource::class)]
class CompaniesTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['name' => 'Acme LLC']
     */
    public function it_lists_companies(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $company = Company::factory()->create(['name' => 'Acme LLC']);

        Livewire::test(ListCompanies::class)
            ->actingAs($this->superAdmin())
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($company);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Rocket Corp']
     */
    public function it_creates_a_company(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $payload = ['name' => 'Rocket Corp'];

        Livewire::test(CreateCompany::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_company_when_name_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $payload = [];

        Livewire::test(CreateCompany::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Updated Corp']
     */
    public function it_updates_a_company(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $company = Company::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'Updated Corp'];

        Livewire::test(EditCompany::class, ['record' => $company->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_company(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $company = Company::factory()->create();

        Livewire::test(ListCompanies::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $company);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
