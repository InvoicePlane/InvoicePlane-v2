<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Users\Pages\ListUsers;
use Modules\Core\Filament\Admin\Resources\Users\UserResource;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserResource::class)]
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
    #[Group('crud')]
    public function it_lists_users(): void
    {
        /* arrange */
        $user = User::factory()->create(['email' => 'admin@example.com']);

        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListUsers::class);

        /* assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('users', $user->toArray());
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_user_twice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        /* @arrange deleted user */
        $user = User::factory()->create();
        $user->delete();

        /* @act try to delete again */
        /* act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListUsers::class)->callTableAction('delete', $user);

        /* assert */
        $component->assertHasErrors();

        /* @assert form error triggered */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
    # endregion
    # region multi-tenancy
    #[Test]
    #[Group('crud')]
    public function it_cannot_access_users_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s user.');
    }
    # endregion


    #region spicy
    # endregion
}
