<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Filament\Resources\ClientResource\Pages\CreateClient;
use Modules\Clients\Filament\Resources\ClientResource\Pages\ManageClients;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Throwable;

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

        $response = $this->actingAs($user, 'web')->get(route('filament.ivpl.resources.clients.index'));

        $response->assertStatus(200);
        $response->assertSee('::client_name::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_active_clients_index(): void
    {
        $this->markTestIncomplete('active/inactive clients not filtered yet, make tab for active clients?');

        $user = User::factory()->create();
        Client::factory()->create([
            'client_name'   => '::active_client_name::',
            'client_active' => true,
        ]);

        Client::factory()->inactive()->create([
            'client_name' => '::inactive_client_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.clients.index', ['status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('::active_client_name::');
        $response->assertDontSee('::inactive_client_name::');
    }

    /**
     * @test
     */
    public function it_shows_only_filtered_inactive_clients_index(): void
    {
        $this->markTestIncomplete('active/inactive clients not filtered yet, make tab for active clients?');
        $user = User::factory()->create();
        Client::factory()->create([
            'client_name'   => '::active_client_name::',
            'client_active' => true,
        ]);

        Client::factory()->inactive()->create([
            'client_name' => '::inactive_client_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.clients.index', ['status' => 'inactive']));
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

        $response = $this->actingAs($user, 'web')->get(route('filament.ivpl.resources.clients.index'));

        $response->assertStatus(200);
        $response->assertSee('::active_client_name::');
        $response->assertSee('::inactive_client_name::');
    }

    /** @test */
    public function it_creates_a_client(): void
    {
        $data = [
            'client_name'          => 'Test Client',
            'client_email'         => 'client@example.com',
            'client_phone'         => '123456789',
            'client_date_created'  => now()->toDateTimeString(),
            'client_date_modified' => now()->toDateTimeString(),
            'client_active'        => true,
        ];

        try {
            Livewire::test(CreateClient::class)
                ->set('data.client_name', $data['client_name'])
                ->set('data.client_email', $data['client_email'])
                ->set('data.client_phone', $data['client_phone'])
                ->set('data.client_date_created', $data['client_date_created'])
                ->set('data.client_date_modified', $data['client_date_modified'])
                ->set('data.client_active', $data['client_active'])
                ->call('create')
                ->assertHasNoErrors();

            $this->assertDatabaseHas(Client::class, $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dump($e->validator->errors()->toArray()); // Dump validation errors if any
            $this->fail('Validation failed: ' . json_encode($e->validator->errors()->toArray()));
        } catch (Throwable $e) {
            dump($e->getMessage()); // Dump any other unexpected errors
            throw $e;
        }
    }

    /** @test */
    public function it_edits_a_client(): void
    {
        $client = Client::factory()->create([
            'client_name'  => 'Original Name',
            'client_email' => 'original@example.com',
        ]);

        $updatedData = [
            'client_name'  => 'Updated Name',
            'client_email' => 'updated@example.com',
        ];

        Livewire::test(ManageClients::class)
            ->callTableAction('edit', $client, $updatedData)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', $updatedData);
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
    public function it_possibly_creates_a_client(): void
    {
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

        // $this->authenticated();

        $response = $this->postJson(route('filament.ivpl.resources.clients.store'), $payload);
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
    public function it_possibly_updates_a_client(): void
    {
        // Payload for updating client
        $payload = [
            'name'  => 'Updated Client',
            'email' => 'updatedclient@example.com',
            'phone' => '0987654321',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('filament.ivpl.resources.clients.update', ['record' => 1]), $payload);
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

        $response = $this->deleteJson(route('filament.ivpl.resources.clients.delete', ['record' => 1]));
        $response->assertStatus(200);
    }
}
