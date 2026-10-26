<?php

namespace Modules\Core\Tests\Feature\Admin;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions\AssignUserBulkAction;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class CompanyUserBulkActionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_assigns_user_to_multiple_companies(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(2)->create();
        $user      = User::factory()->create(['is_active' => true]);
        $ids       = $companies->pluck('id')->all();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($ids)
            ->callAction(TestAction::make('assignUser')->table()->bulk(), ['user_id' => $user->id])
            ->assertHasNoFormErrors();

        /* Assert */
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_user', [
                'company_id' => $company->id,
                'user_id'    => $user->id,
            ]);
        }
    }

    #[Test]
    public function it_does_not_duplicate_the_pivot_row_when_the_user_is_already_assigned(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $user    = User::factory()->create(['is_active' => true]);
        $company->users()->attach($user->id);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignUser')->table()->bulk(), ['user_id' => $user->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_user', 1);
    }

    #[Test]
    public function it_assigns_the_same_user_to_multiple_companies_without_affecting_existing_assignments(): void
    {
        /* Arrange */
        $existingCompany = Company::factory()->create();
        $newCompany      = Company::factory()->create();
        $user            = User::factory()->create(['is_active' => true]);
        $existingCompany->users()->attach($user->id);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$newCompany->id])
            ->callAction(TestAction::make('assignUser')->table()->bulk(), ['user_id' => $user->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_user', 2);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $existingCompany->id,
            'user_id'    => $user->id,
        ]);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $newCompany->id,
            'user_id'    => $user->id,
        ]);
    }

    #[Test]
    public function it_lists_users_with_name_and_email_disambiguation(): void
    {
        /* Arrange */
        $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        /* Act */
        $options = AssignUserBulkAction::getUserOptions();

        /* Assert */
        $this->assertSame('Jane Doe (jane@example.com)', $options[$user->id]);
    }

    #[Test]
    public function it_requires_a_user_to_be_selected(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act & Assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignUser')->table()->bulk(), ['user_id' => null])
            ->assertHasFormErrors(['user_id' => 'required']);

        $this->assertDatabaseCount('company_user', 0);
    }

    #[Test]
    public function it_assigns_the_user_to_every_selected_company_in_one_bulk_call(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(3)->create();
        $user      = User::factory()->create(['is_active' => true]);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($companies->pluck('id')->all())
            ->callAction(TestAction::make('assignUser')->table()->bulk(), ['user_id' => $user->id])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_user', 3);
    }
}
