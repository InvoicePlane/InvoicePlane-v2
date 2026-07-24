<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Company\Pages\CompanyUsers;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CompanyUsers::class)]
class CompanyUsersTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_the_companys_users(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanyUsers::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee($this->user->email);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_does_not_list_users_from_another_company(): void
    {
        /* Arrange */
        $otherUser = User::factory()->withCompany(['search_code' => 'OTHERCO'])->create();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CompanyUsers::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertDontSee($otherUser->email);
    }
    # endregion

    # region add / remove
    #[Test]
    #[Group('crud')]
    public function it_adds_an_existing_user_to_the_company_by_email(): void
    {
        /* Arrange */
        $newUser = User::factory()->create();

        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanyUsers::class)
            ->mountTableAction('add_user')
            ->setTableActionData(['user_id' => $newUser->id])
            ->callMountedTableAction();

        /* Assert */
        $this->assertDatabaseHas('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $newUser->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_refuses_to_attach_a_user_who_is_already_a_member(): void
    {
        /* Arrange */
        $alreadyMember = User::factory()->create();
        $this->company->users()->attach($alreadyMember);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanyUsers::class)
            ->mountTableAction('add_user')
            ->setTableActionData(['user_id' => $alreadyMember->id])
            ->callMountedTableAction();

        /* Assert: still exactly one pivot row, not duplicated */
        $this->assertDatabaseCount('company_user', 2); // acting user + already-member
        $this->assertDatabaseHas('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $alreadyMember->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_removes_a_user_from_the_company(): void
    {
        /* Arrange */
        $secondUser = User::factory()->create();
        $this->company->users()->attach($secondUser);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanyUsers::class)
            ->callTableAction('remove', $secondUser);

        /* Assert */
        $this->assertDatabaseMissing('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $secondUser->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_refuses_to_remove_the_last_remaining_user_of_a_company(): void
    {
        /* Act */
        Livewire::actingAs($this->user)
            ->test(CompanyUsers::class)
            ->callTableAction('remove', $this->user);

        /* Assert: still attached */
        $this->assertDatabaseHas('company_user', [
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
        ]);
    }
    # endregion
}
