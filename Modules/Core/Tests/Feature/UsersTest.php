<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Users\Pages\ListUsers;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListUsers::class)]
class UsersTest extends AbstractAdminPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['email' => 'admin@example.com']
     *
     * @arrange create a user with email 'admin@example.com'
     *
     * @act visit user listing
     *
     * @assert email is visible
     */
    public function it_lists_users(): void
    {
        /* arrange */
        $user = User::factory()->create(['email' => 'admin@example.com']);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListUsers::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('users', $user->toArray());
    }
    # endregion

    # region modals
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "id": 1
     * }
     */
    public function it_deletes_a_user(): void
    {
        /* arrange */
        $user = User::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListUsers::class)
            ->mountAction(TestAction::make('delete')->table($user))
            ->callMountedAction();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "id": 1
     * }
     */
    public function it_fails_to_delete_user_twice(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

        /* arrange */
        $user = User::factory()->create();

        /* @arrange deleted user */
        $user->delete();

        /* @act try to delete again */
        /* act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListUsers::class)
            ->mountAction(TestAction::make('delete')->table($user))
            ->callMountedAction();

        /* assert */
        $component->assertHasErrors();

        /* @assert form error triggered */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
