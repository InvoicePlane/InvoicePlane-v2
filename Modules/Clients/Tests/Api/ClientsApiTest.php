<?php

namespace Modules\Clients\Tests\Api;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;
use PHPUnit\Framework\Attributes\Group;

// use Laravel\Sanctum\Sanctum;

class ClientsApiTest extends AbstractTestCase
{
    use ApiTestTrait;
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

    public function route_is_403_for_not_authenticated(): void
    {
        $this->expectException(AuthenticationException::class);
        $response = $this->getJson(route('api.clients.index'));
        $response->assertStatus(403);
    }

    public function route_is_401_for_guest_user(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->markTestIncomplete();
        $user = User::factory(['user_type' => 2])->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.clients.index'));
        $response->assertUnauthorized();
    }

    #[Group('crud')]
    public function it_returns_clients_index(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $user = User::factory(['user_type' => 1])->create();
        Sanctum::actingAs($user);

        Relation::factory()->count(5)->create();
        $response = $this->get(route('api.clients.index'));
        $response->assertSuccessful();

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'client_active',
                        'company',
                        'name',
                        'client_surname',
                        'client_language',
                        'client_gender',
                        'client_birthdate',
                    ],
                ],
                'message',
            ]);
    }

    public function test_read_client(): void
    {
        $user = User::factory(['user_type' => 1])->create();
        Sanctum::actingAs($user);

        $client   = Relation::factory()->create();
        $response = $this->getJson(
            route(
                'api.clients.show',
                ['client' => $client->client_id]
            )
        );

        $response->assertJsonFragment([
            'company'          => $client->client_name,
            'client_surname'   => $client->client_surname,
            'client_birthdate' => $client->client_birthdate,
        ]);
    }

    public function test_create_client(): void
    {
        $user = User::factory(['user_type' => 1])->create();
        Sanctum::actingAs($user);

        $client = Relation::factory()->make()->toArray();

        $response = $this->postJson(
            route('api.clients.store'),
            $client
        );

        unset($client['client_date_created'], $client['client_date_modified']);

        $response->assertJsonFragment($client);
    }

    public function test_create_client_missing_required_field(): void
    {
        $user = User::factory()->create(['user_type' => 1]);
        Sanctum::actingAs($user);

        $client = Relation::factory()->make(['client_name' => null]);

        $response = $this->postJson(
            route('api.clients.store'),
            $client->toArray()
        );

        $response->assertUnprocessable();
    }

    public function test_put_update_client(): void
    {
        $user = User::factory(['user_type' => 1])->create();
        Sanctum::actingAs($user);

        $client       = Relation::factory()->create();
        $editedClient = Relation::factory()->make()->toArray();

        $response = $this->putJson(
            route('api.clients.update', ['client' => $client->client_id]),
            $editedClient
        );

        unset($editedClient['client_date_created'], $editedClient['client_date_modified']);

        $response->assertJsonFragment($editedClient);
    }

    public function test_patch_update_client(): void
    {
        $this->markTestIncomplete();
        $user = User::factory(['user_type' => 1])->create();
        Sanctum::actingAs($user);

        $client       = Relation::factory()->create();
        $editedClient = Relation::factory()->make()->toArray();

        $response = $this->patchJson(
            route('api.clients.update', ['client' => $client->client_id]),
            $editedClient
        );

        unset($editedClient['client_date_created'], $editedClient['client_date_modified']);

        $response->assertJsonFragment($editedClient);
    }

    public function test_delete_client(): void
    {
        $this->markTestIncomplete();
        $user = User::factory(['user_type' => 1])->create();
        Sanctum::actingAs($user);

        $client = Relation::factory()->make()->toArray();

        $response_created = $this->post(route('api.clients.store', $client));
        $response_created->assertSuccessful();

        $response_deleted = $this->deleteJson(route('api.clients.destroy', ['client' => $response_created->json()['data']['id']]));
        $response_deleted->assertSuccessful();

        $response_not_found = $this->getJson(
            route(
                'api.clients.show',
                ['client' => $response_created->json()['data']['id']]
            )
        );

        $response_not_found->assertNotFound();
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
    #[Group('crud')]
    public function it_creates_a_client_via_api(): void
    {
        $this->markTestIncomplete();

        /* arrange */

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

        $response = $this->postJson(route('api.clients.store'), $payload);
        $response->assertSuccessful();
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
    #[Group('crud')]
    public function it_updates_a_client_via_api(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // Payload for updating client
        $payload = [
            'name'  => 'Updated Client',
            'email' => 'updatedclient@example.com',
            'phone' => '0987654321',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('api.clients.update', ['record' => 1]), $payload);
        $response->assertSuccessful();
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     *
     * No payload required for deleting a client.
     */
    #[Group('crud')]
    public function it_deletes_a_client_via_api(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('api.clients.delete', ['record' => 1]));
        $response->assertSuccessful();
    }
}
