<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region smoke
    /** @test */
    public function it_lists_users(): void
    {
        $user = User::factory()->create();

        User::factory()->create([
            'user_name' => '::user_name::',
        ]);

        Livewire::test(ListUsers::class)
            ->assertStatus(200)
            ->assertSee('::user_name::');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "name": "Example",
     * "email": "Example",
     * "email_verified_at": "2025-04-30",
     * "password": "Example",
     * "remember_token": "Example"
     * }
     */
    public function it_creates_a_user(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
            'name'              => 'Example',
            'email'             => 'Example',
            'email_verified_at' => '2025-04-30',
            'password'          => 'Example',
            'remember_token'    => 'Example',
        ];

        Livewire::test(CreateUser::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "user_type": null,
     *    "email": null,
     *    "user_password": null
     * }
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_a_user_without_required_fields(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();
        $payload = [
            'user_type'  => null,
            'user_email' => null,
            'password'   => null,
        ];

        Livewire::test(CreateUser::class)
            ->assertStatus(200)
            ->set('data.user_type', $payload['user_type'])
            ->set('data.user_email', $payload['user_email'])
            ->set('data.user_password', $payload['password'])
            ->call('create')
            ->assertHasErrors(['data.user_type'])
            ->assertHasErrors(['data.user_email'])
            ->assertHasErrors(['data.user_password']);

        $this->assertDatabaseHas('users', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "name": "Example",
     * "email": "Example",
     * "email_verified_at": "2025-04-30",
     * "password": "Example",
     * "remember_token": "Example"
     * }
     */
    public function it_updates_a_user(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = User::factory()->create();

        $this->markTestSkipped();
        // $this->authenticate();

        $user = User::factory()->create();

        $updatedData = [
            'user_name'    => 'updated_user',
            'user_company' => 'Updated Inc',
        ];

        Livewire::test(EditUser::class, ['record' => $user->user_id])
            ->set('data.user_name', $updatedData['user_name'])
            ->set('data.user_company', $updatedData['user_company'])
            ->call('save')
            ->assertStatus(200);

        $this->assertDatabaseHas('users', array_merge($updatedData, [
            'user_id' => $user->user_id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "name": "Example",
     * "email": "Example",
     * "email_verified_at": "2025-04-30",
     * "password": "Example",
     * "remember_token": "Example"
     * }
     */
    public function it_deletes_a_user(): void
    {
        $this->markTestIncomplete('Needs delete action');
        // $this->authenticate();
        $user = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callTableAction('delete', $user->user_id)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['user_id' => $user->user_id]);
    }

    // endregion

    // region usp
    /**
     * @test
     *
     * @payload
     * {
     * "user_id": 1
     * }
     *
     * @skip Not implemented yet
     */
    public function it_assigns_clients_to_a_guest_user(): void
    {
        $this->markTestSkipped();
        $guestUser = User::factory()->create([
            'user_type'   => User::CLIENT,
            'user_name'   => '::user_name::',
            'user_email'  => '::user_email::',
            'user_active' => true,
        ]);

        $clients = Client::factory()->count(3)->create();

        Livewire::test(ManageClients::class, ['userId' => $guestUser->user_id])
            ->assertStatus(200)
            ->assertSee('Assigned Clients')
            ->call('addClient', $clients[0]->client_id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_clients', [
            'user_id'   => $guestUser->user_id,
            'client_id' => $clients[0]->client_id,
        ]);

        Livewire::test(ManageClients::class, ['userId' => $guestUser->user_id])
            ->call('assignAllClients')
            ->assertHasNoErrors();

        foreach ($clients as $client) {
            $this->assertDatabaseHas('user_clients', [
                'user_id'   => $guestUser->user_id,
                'client_id' => $client->client_id,
            ]);
        }
    }
    // endregion
}
