<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CompanyResource.
     *
     * @payload
     * {
     * "search_code": "Example",
     * "name": "Example",
     * "slug": "Example",
     * "vat_number": "Example",
     * "id_number": "Example",
     * "coc_number": "Example"
     * }
     */
    public function it_creates_a_company(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'search_code' => 'Example',
            'name'        => 'Example',
            'slug'        => 'Example',
            'vat_number'  => 'Example',
            'id_number'   => 'Example',
            'coc_number'  => 'Example',
        ];

        Livewire::test(CreateCompany::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CompanyResource.
     *
     * @payload
     * {
     * "search_code": "Example",
     * "name": "Example",
     * "slug": "Example",
     * "vat_number": "Example",
     * "id_number": "Example",
     * "coc_number": "Example"
     * }
     */
    public function it_updates_a_company(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Company::factory()->create();

        $payload = [
            'search_code' => 'Example',
            'name'        => 'Example',
            'slug'        => 'Example',
            'vat_number'  => 'Example',
            'id_number'   => 'Example',
            'coc_number'  => 'Example',
        ];

        Livewire::test(EditCompany::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Core\Filament\Admin\Resources\CompanyResource.
     *
     * @payload
     * {
     * "search_code": "Example",
     * "name": "Example",
     * "slug": "Example",
     * "vat_number": "Example",
     * "id_number": "Example",
     * "coc_number": "Example"
     * }
     */
    public function it_deletes_a_company(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Company::factory()->create();

        Livewire::test(ListCompanies::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('companies', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
