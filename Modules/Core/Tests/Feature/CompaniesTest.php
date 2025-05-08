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
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create(['name' => 'Acme LLC']);

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(ListCompanies::class);

/** assert */
$component->assertSuccessful()->assertSeeDatabaseRecords($company);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Rocket Corp']
     */
    public function it_creates_a_company(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['name' => 'Rocket Corp'];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(CreateCompany::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_create_company_when_name_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = [];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(CreateCompany::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasFormErrors(['name']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['name' => 'Updated Corp']
     */
    public function it_updates_a_company(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'Updated Corp'];

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(EditCompany::class, ['record' => $company->id])->fillForm($payload)->call('save');

/** assert */
$component->assertHasNoFormErrors();

        $this->assertDatabaseHas('companies', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_a_company(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();

        /** act */
$component = Livewire::actingAs($this->superAdmin())->test(ListCompanies::class)->callTableAction('delete', $company);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
