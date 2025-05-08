<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\UserResource;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages\EditUser;
use Modules\Core\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
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
    #[Group('crud')]
    public function it_lists_users(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $record = User::factory()->create(['email' => 'admin@example.com']);

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListUsers::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($record);
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
    #[Group('crud')]
    public function it_creates_a_user(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['email' => 'new@example.com', 'password' => 'password123'];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateUser::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

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
    #[Group('crud')]
    public function it_fails_to_create_user_without_email(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['password' => 'abc'];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(CreateUser::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['email']);
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
    #[Group('crud')]
    public function it_updates_a_user(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $user    = User::factory()->create(['email' => 'before@example.com']);
        $payload = ['email' => 'updated@example.com'];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditUser::class, ['record' => $user->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

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
    #[Group('crud')]
    public function it_fails_to_update_user_without_email(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $user    = User::factory()->create(['email' => 'valid@example.com']);
        $payload = ['email' => null];

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(EditUser::class, ['record' => $user->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['email']);
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
    #[Group('crud')]
    public function it_deletes_a_user(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $user = User::factory()->create();

        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListUsers::class)->callTableAction('delete', $user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    #[Group('crud')]
    public function it_fails_to_delete_user_twice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        /* @arrange deleted user */
        $user = User::factory()->create();
        $user->delete();

        /* @act try to delete again */
        /** act */
        $component = Livewire::actingAs($this->superAdmin())->test(ListUsers::class)->callTableAction('delete', $user);

        /* assert */
        $component->assertHasErrors();

        /* @assert form error triggered */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
