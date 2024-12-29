<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;

class ClientsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;
    // endregion

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests
    /**
     * @test
     */
    public function it_shows_clients_index(): void
    {
        $user = User::factory()->create();
        Client::factory()->create([
            'client_name' => '::client_name::',
        ]);
        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.resources.clients.index'));
        $response->assertStatus(200);
        $response->assertSee('::client_name::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_active_clients_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();
        Client::factory()->create([
            'client_name'   => '::active_client_name::',
            'client_active' => true,
        ]);

        Client::factory()->inactive()->create([
            'client_name' => '::inactive_client_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.resources.clients.index', ['status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('::active_client_name::');
        $response->assertDontSee('::inactive_client_name::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_inactive_clients_index(): void
    {
        $this->markTestIncomplete();
        $user = User::factory()->create();
        Client::factory()->create([
            'client_name'   => '::active_client_name::',
            'client_active' => true,
        ]);

        Client::factory()->inactive()->create([
            'client_name' => '::inactive_client_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.resources.clients.index', ['status' => 'inactive']));
        $response->assertStatus(200);
        $response->assertSee('::inactive_client_name::');
        $response->assertDontSee('::active_client_name::');
    }

    /**
     * @test
     */
    public function it_shows_all_clients_index(): void
    {
        $user = User::factory()->create();
        Client::factory()->create([
            'client_name'   => '::active_client_name::',
            'client_active' => true,
        ]);

        Client::factory()->inactive()->create([
            'client_name' => '::inactive_client_name::',
        ]);

        $response = $this->actingAs($user, 'web')->get(route('filament.resources.clients.index', ['status' => 'all']));
        $response->assertStatus(200);
        $response->assertSee('::active_client_name::');
        $response->assertSee('::inactive_client_name::');
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for creating a client:
     * {
     *     "name": "Test Client",
     *     "email": "testclient@example.com",
     *     "phone": "1234567890",
     *     "address": "123 Test Street",
     *     "city": "Test City",
     *     "state": "Test State",
     *     "zip": "12345",
     *     "country": "Test Country"
     * }
     */
    public function it_creates_a_client(): void
    {
        // Payload for the create client request
        $payload = [
            'name'    => 'Test Client',
            'email'   => 'testclient@example.com',
            'phone'   => '1234567890',
            'address' => '123 Test Street',
            'city'    => 'Test City',
            'state'   => 'Test State',
            'zip'     => '12345',
            'country' => 'Test Country',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('filament.resources.clients.create'), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * Payload for updating a client:
     * {
     *     "name": "Updated Client",
     *     "email": "updatedclient@example.com",
     *     "phone": "0987654321"
     * }
     */
    public function it_updates_a_client(): void
    {
        // Payload for updating client
        $payload = [
            'name'  => 'Updated Client',
            'email' => 'updatedclient@example.com',
            'phone' => '0987654321',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('filament.resources.clients.update', ['record' => 1]), $payload);
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * No payload required for deleting a client.
     */
    public function it_deletes_a_client(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('filament.resources.clients.delete', ['record' => 1]));
        $response->assertStatus(200);
    }
}
