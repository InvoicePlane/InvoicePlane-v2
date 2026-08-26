<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Company\Resources\CompanyUsers\Pages\ListCompanyUsers;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListCompanyUsers::class)]
class CompanyUsersTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_team_members_of_the_current_company(): void
    {
        // Note: like the two "remove" tests below, this has been observed
        // to fail only when run alongside other test classes, never alone
        // — see the note on it_removes_a_team_member_from_the_company().
        /* Arrange */
        $member = User::factory()->create(['name' => 'Existing Member']);
        $this->company->users()->attach($member->id);

        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class);

        /* Assert */
        $component->assertSuccessful()
            ->assertCanSeeTableRecords(collect([$member]));
    }

    #[Test]
    #[Group('smoke')]
    public function it_does_not_list_users_belonging_to_other_companies(): void
    {
        /* Arrange */
        $unrelatedUser = User::factory()->withCompany(['search_code' => 'OTHERCO'])->create();

        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class);

        /* Assert */
        $component->assertSuccessful()
            ->assertCanNotSeeTableRecords(collect([$unrelatedUser]));
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_adds_an_existing_unattached_user_as_a_team_member(): void
    {
        /* Arrange — regression guard: ListCompanyUsers previously called an
         * undefined Company::getTenant() method, so this action could never
         * succeed for any user at all. */
        $newMember = User::factory()->create(['email' => 'unattached@example.test']);

        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class)
            ->mountAction('add_user')
            ->fillForm(['email' => 'unattached@example.test'])
            ->callMountedAction();

        /* Assert */
        $component->assertNotified(trans('ip.team_member_added'));
        $this->assertDatabaseHas('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $newMember->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function adding_a_team_member_twice_does_not_duplicate_the_pivot_row(): void
    {
        /* Arrange */
        $member = User::factory()->create(['email' => 'already-member@example.test']);
        $this->company->users()->attach($member->id);

        /* Act */
        $this->testLivewire(ListCompanyUsers::class)
            ->mountAction('add_user')
            ->fillForm(['email' => 'already-member@example.test'])
            ->callMountedAction();

        /* Assert */
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('company_user')
            ->where('company_id', $this->company->id)
            ->where('user_id', $member->id)
            ->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_reports_user_not_found_for_an_email_that_does_not_exist_instead_of_erroring(): void
    {
        /* Arrange */
        $rowsBefore = \Illuminate\Support\Facades\DB::table('company_user')->count();

        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class)
            ->mountAction('add_user')
            ->fillForm(['email' => 'nobody-by-this-email@example.test'])
            ->callMountedAction();

        /* Assert */
        $component->assertNotified(trans('ip.user_not_found'));
        $this->assertSame($rowsBefore, \Illuminate\Support\Facades\DB::table('company_user')->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_add_a_team_member_without_required_email(): void
    {
        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class)
            ->mountAction('add_user')
            ->fillForm(['email' => null])
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['email' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_add_a_team_member_with_an_invalid_email_format(): void
    {
        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class)
            ->mountAction('add_user')
            ->fillForm(['email' => 'not-an-email'])
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['email' => 'email']);
    }

    #[Test]
    #[Group('crud')]
    public function it_removes_a_team_member_from_the_company(): void
    {
        // Note: this test (and the one below) has been observed to fail
        // when run thousands of tests deep in a single full-suite process,
        // despite passing reliably alone or in small groups — the mounted
        // table action's injected $record does not match the intended
        // target. Not reproducible via any change to test data/order within
        // this file; matches the profile of the already-documented
        // "known issue" in .github/DOCKER.md / CLAUDE.md (#689): Livewire
        // test-harness state that only misbehaves at large scale, root
        // cause not yet isolated. Sanity-check against a small filtered run
        // before trusting a failure here from a full-suite run.
        /* Arrange */
        $member = User::factory()->create();
        $this->company->users()->attach($member->id);

        /* Act */
        $component = $this->testLivewire(ListCompanyUsers::class)
            ->mountAction(TestAction::make('remove')->table($member))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $member->id,
        ]);
        // Removing a team member detaches the pivot only — the User
        // record itself (which may belong to other companies) must survive.
        $this->assertDatabaseHas('users', ['id' => $member->id]);
    }

    #[Test]
    #[Group('crud')]
    public function removing_a_team_member_does_not_affect_their_membership_in_other_companies(): void
    {
        /* Arrange */
        $member       = User::factory()->create();
        $otherCompany = \Modules\Core\Models\Company::factory()->create(['search_code' => 'OTHER2']);
        $this->company->users()->attach($member->id);
        $otherCompany->users()->attach($member->id);

        /* Act */
        $this->testLivewire(ListCompanyUsers::class)
            ->mountAction(TestAction::make('remove')->table($member))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $member->id,
        ]);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $otherCompany->id,
            'user_id'    => $member->id,
        ]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    # endregion
}
