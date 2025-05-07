<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Tests\Feature\UsersTest;

use Modules\Core\Filament\Admin\Resources\UserResource\Pages\CreateUser;

use Modules\Core\Filament\Admin\Resources\UserResource\Pages\ListUsers;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Filament\Admin\Resources\UserResource\Pages\EditUser;

use Modules\Core\Models\User;

use Modules\Core\Filament\Admin\Resources\UserResource;

use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserResource::class)]
class UsersTest extends AbstractTestCase
{
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
        $record = User::factory()->create(['email' => 'admin@example.com']);

        Livewire::test(ListUsers::class)
            ->actingAs($this->superAdmin())
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['email' => 'new@example.com', 'password' => 'password123']
     *
     * @arrange none
     *
     * @act create user
     *
     * @assert inserted in database
     */
    public function it_creates_a_user(): void
    {
        $payload = ['email' => 'new@example.com', 'password' => 'password123'];

        Livewire::test(CreateUser::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['email' => null]
     *
     * @arrange none
     *
     * @act try to create without email
     *
     * @assert form error triggered
     */
    public function it_fails_to_create_user_without_email(): void
    {
        $payload = ['password' => 'abc'];

        Livewire::test(CreateUser::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['email' => 'updated@example.com']
     *
     * @arrange user exists
     *
     * @act update email
     *
     * @assert email updated in database
     */
    public function it_updates_a_user(): void
    {
        $user    = User::factory()->create(['email' => 'before@example.com']);
        $payload = ['email' => 'updated@example.com'];

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['email' => null]
     *
     * @arrange user exists
     *
     * @act try to update with null email
     *
     * @assert form error triggered
     */
    public function it_fails_to_update_user_without_email(): void
    {
        $user    = User::factory()->create(['email' => 'valid@example.com']);
        $payload = ['email' => null];

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['email']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     *
     * @arrange user exists
     *
     * @act delete user
     *
     * @assert record removed
     */
    public function it_deletes_a_user(): void
    {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_user_twice(): void
    {
        /* @arrange deleted user */
        $user = User::factory()->create();
        $user->delete();

        /* @act try to delete again */
        Livewire::test(ListUsers::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $user)
            ->assertHasErrors();

        /* @assert form error triggered */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
