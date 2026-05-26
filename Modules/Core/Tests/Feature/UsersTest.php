<?php

namespace Modules\Core\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Users\Pages\ListUsers;
use Modules\Core\Filament\Pages\Auth\Login;
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
        /* Arrange */
        $user = User::factory()->create(['email' => 'admin@example.com']);

        /* Act */
        $component = Livewire::actingAs($this->superAdmin())
            ->test(ListUsers::class);

        /* Assert */

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
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
    # endregion

    # region modals
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion

    # region authentication
    #[Test]
    #[Group('authentication')]
    #[Group('security')]
    /**
     * Test that inactive users cannot log in and receive appropriate error messages.
     *
     * @payload ['name' => 'Inactive User', 'email' => 'inactive@example.com', 'is_active' => false]
     */
    public function it_prevents_inactive_users_from_logging_in(): void
    {
        /* Arrange */
        $expectedDate = Carbon::now();

        $inactiveUser = User::factory()->create([
            'name'              => 'Inactive User',
            'email'             => 'inactive@example.com',
            'password'          => bcrypt('password123'),
            'is_active'         => false,
            'email_verified_at' => $expectedDate,
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

        $this->assertEquals(
            'Your account is inactive. Please contact the administrator.',
            trans('ip.account_inactive')
        );

        $this->assertEquals(
            'Login denied: Your account has been deactivated.',
            trans('ip.account_inactive_login_denied')
        );

        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email'     => 'inactive@example.com',
            'is_active' => false,
        ]);
    }

    #[Test]
    #[Group('authentication')]
    #[Group('security')]
    #[Group('edge-cases')]
    /**
     * Test edge case: Active user can log in successfully after inactive user fails.
     */
    public function it_allows_active_users_to_login_after_inactive_user_fails(): void
    {
        /* Arrange */
        $expectedDate = Carbon::now();

        $inactiveUser = User::factory()->create([
            'name'              => 'Inactive User',
            'email'             => 'inactive@example.com',
            'password'          => bcrypt('password123'),
            'is_active'         => false,
            'email_verified_at' => $expectedDate,
        ]);

        $activeUser = User::factory()->create([
            'name'              => 'Active User',
            'email'             => 'active@example.com',
            'password'          => bcrypt('password123'),
            'is_active'         => true,
            'email_verified_at' => $expectedDate,
        ]);

        $inactiveUser->companies()->attach($this->company);
        $activeUser->companies()->attach($this->company);

        /* Act */
        $inactiveResponse = Livewire::test(Login::class)
            ->fillForm([
                'email'    => 'inactive@example.com',
                'password' => 'password123',
            ])
            ->call('authenticate');

        $inactiveResponse->assertHasErrors();
        $this->assertGuest();
    }

    #[Test]
    #[Group('authentication')]
    #[Group('security')]
    #[Group('edge-cases')]
    /**
     * Test edge case: User becomes inactive after being created as active.
     */
    public function it_prevents_login_when_user_becomes_inactive_after_creation(): void
    {
        /* Arrange */
        $expectedDate = Carbon::now();

        $userPayload = [
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'password'          => 'password123',
            'is_active'         => true,
            'email_verified_at' => $expectedDate,
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

        /*if (app()->runningUnitTests()) {
            dd($initialLoginResponse->errors());
        }*/
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
