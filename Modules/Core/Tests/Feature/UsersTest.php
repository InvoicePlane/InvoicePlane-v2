<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Admin\Resources\Users\Pages\ListUsers;
use Modules\Core\Filament\Pages\Auth\Login;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

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
        /* Arrange */
        $user = User::factory()->create(['email' => 'admin@example.com']);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListUsers::class);

        /* Assert */
        $component->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_deletes_a_user(): void
    {
        /* Arrange */
        $user = User::factory()->create();

        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListUsers::class)
            ->mountAction(TestAction::make('delete')->table($user))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
    # endregion

    # region modals
    # endregion

    # region security
    #[Test]
    #[Group('security')]
    public function it_prevents_deletion_of_super_admin_users(): void
    {
        /* Arrange */
        Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
        $adminUser = User::factory()->create();
        $adminUser->assignRole(UserRole::SUPER_ADMIN->value);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListUsers::class)
            ->mountAction(TestAction::make('delete')->table($adminUser))
            ->callMountedAction();

        /* Assert — super_admin record must still exist */
        $component->assertSuccessful();
        $this->assertDatabaseHas('users', ['id' => $adminUser->id]);
    }

    #[Test]
    #[Group('security')]
    public function it_allows_deletion_of_non_admin_users(): void
    {
        /* Arrange */
        Role::query()->firstOrCreate(['name' => UserRole::CUSTOMER_ADMIN->value, 'guard_name' => 'web']);
        $regularUser = User::factory()->create();
        $regularUser->assignRole(UserRole::CUSTOMER_ADMIN->value);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin)
            ->test(ListUsers::class)
            ->mountAction(TestAction::make('delete')->table($regularUser))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseMissing('users', ['id' => $regularUser->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion

    # region authentication
    #[Test]
    #[Group('authentication')]
    #[Group('security')]
    public function it_denies_login_to_inactive_users(): void
    {
        /* Arrange */
        $inactiveUser = User::factory()->create([
            'name'              => 'Inactive User',
            'email'             => 'inactive@example.com',
            'password'          => bcrypt('password123'),
            'is_active'         => false,
            'email_verified_at' => Carbon::now(),
        ]);

        $inactiveUser->companies()->attach($this->company);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'inactive@example.com',
                'password' => 'password123',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertHasErrors();
        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email'     => 'inactive@example.com',
            'is_active' => false,
        ]);
    }

    #[Test]
    #[Group('authentication')]
    #[Group('security')]
    public function it_allows_active_users_to_login(): void
    {
        /* Arrange */
        $activeUser = User::factory()->create([
            'name'              => 'Active User',
            'email'             => 'active@example.com',
            'password'          => bcrypt('password'),
            'is_active'         => true,
            'email_verified_at' => Carbon::now(),
        ]);

        $activeUser->companies()->attach($this->company);

        /* Act */
        $response = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'active@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        /* Assert */
        $response->assertHasNoErrors();
        $this->assertAuthenticated();
    }

    #[Test]
    #[Group('authentication')]
    #[Group('security')]
    #[Group('edge-cases')]
    public function it_prevents_login_when_user_becomes_inactive_after_creation(): void
    {
        /* Arrange */
        $userPayload = [
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'password'          => 'password123',
            'is_active'         => true,
            'email_verified_at' => Carbon::now(),
        ];

        $user = User::factory()->create($userPayload);

        $user->companies()->attach($this->company);
        $user->refresh();

        $initialLoginResponse = Livewire::test(Login::class)
            ->fillForm([
                'email'    => $userPayload['email'],
                'password' => $userPayload['password'],
            ])
            ->call('authenticate');

        $initialLoginResponse->assertSuccessful();
        $this->assertAuthenticated();

        auth()->logout();
        $this->assertGuest();

        /* Act */
        $user->update(['is_active' => false]);

        $secondLoginResponse = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'test@example.com',
                'password' => 'password123',
            ])
            ->call('authenticate');

        /* Assert */
        $secondLoginResponse->assertHasErrors();
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email'     => $userPayload['email'],
            'is_active' => false,
        ]);
    }
    # endregion
}
