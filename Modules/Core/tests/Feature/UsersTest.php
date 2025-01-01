<?php

namespace Modules\Core\tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Filament\Resources\ClientResource\Pages\ManageClients;
use Modules\Clients\Models\Client;
use Modules\Core\Filament\Resources\UserResource\Pages\CreateUser;
use Modules\Core\Filament\Resources\UserResource\Pages\EditUser;
use Modules\Core\Filament\Resources\UserResource\Pages\ManageUsers;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;

/** @group features */
class UsersTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_shows_users_index(): void
    {
        $user = User::factory()->create();

        User::factory()->create([
            'user_name' => '::user_name::',
        ]);

        Livewire::test(ManageUsers::class)
            ->assertStatus(200)
            ->assertSee('::user_name::');
    }

    /**
     * @test
     *
     * @payload
     * {
     * "user_type": "client",
     * "user_active": true,
     * "user_name": "jdoe",
     * "user_company": "Example Inc",
     * "user_address_1": "123 Main Street",
     * "user_city": "Somewhere",
     * "user_country": "USA",
     * "email": "jdoe@example.com",
     * "user_password": "securepassword",
     * "user_language": "en"
     * }
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_user(): void
    {
        $this->markTestSkipped();
        // $this->authenticate();

        $payload = [
            'user_type'     => User::ADMIN,
            'user_language' => '::maybe_english::',
            'user_name'     => '::user_name::',
            'user_company'  => '::localhost corporation::',
            'user_password' => '::password::',
            'user_email'    => 'email@email.com',
        ];

        Livewire::test(CreateUser::class)
            ->assertStatus(200)
            ->set('data.user_type', $payload['user_type'])
            ->set('data.user_language', $payload['user_language'])
            ->set('data.user_name', $payload['user_name'])
            ->set('data.user_company', $payload['user_company'])
            ->set('data.user_email', $payload['user_email'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', $payload);
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
            'user_type'     => null,
            'user_email'    => null,
            'user_password' => null,
        ];

        Livewire::test(CreateUser::class)
            ->assertStatus(200)
            ->set('data.user_type', $payload['user_type'])
            ->set('data.user_email', $payload['user_email'])
            ->set('data.user_password', $payload['user_password'])
            ->call('create')
            ->assertHasErrors(['data.user_type'])
            ->assertHasErrors(['data.user_email'])
            ->assertHasErrors(['data.user_password']);

        $this->assertDatabaseHas('users', $payload);
    }

    /**
     * @test
     *
     * @payload
     * {
     * "user_name": "updated_user",
     * "user_company": "Updated Inc"
     * }
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_user(): void
    {
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
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', array_merge($updatedData, [
            'user_id' => $user->user_id,
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_bulk_deletes_users(): void
    {
        $users = User::factory()->count(3)->create();

        Livewire::test(ManageUsers::class)
            ->callTableBulkAction('delete', $users)
            ->assertHasNoErrors();

        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', [
                'user_id' => $user->user_id,
            ]);
        }
    }

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

    // region Custom Tests
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
