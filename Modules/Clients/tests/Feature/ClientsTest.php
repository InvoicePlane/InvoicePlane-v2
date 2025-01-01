<?php

namespace Modules\Clients\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Filament\Resources\ClientResource\Pages\CreateClient;
use Modules\Clients\Filament\Resources\ClientResource\Pages\EditClient;
use Modules\Clients\Filament\Resources\ClientResource\Pages\ManageClients;
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
     *
     * @skip Not implemented yet
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
     *
     * @skip Not implemented yet
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
     *
     * @skip Not implemented yet
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

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_mutates_form_data_before_creating(): void
    {
        $data = [
            'client_name'  => 'Test Client',
            'client_email' => 'client@example.com',
        ];

        Livewire::test(CreateClient::class)
            ->set('data.client_name', $data['client_name'])
            ->set('data.client_email', $data['client_email'])
            ->call('create');

        $this->assertDatabaseHas(Client::class, [
            'client_name'          => 'Test Client',
            'client_email'         => 'client@example.com',
            'client_date_created'  => now()->toDateTimeString(),
            'client_date_modified' => now()->toDateTimeString(),
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_client(): void
    {
        $data = [
            'client_name'   => 'Test Client',
            'client_email'  => 'client@example.com',
            'client_phone'  => '123456789',
            'client_active' => true,
        ];

        Livewire::test(CreateClient::class)
            ->set('data.client_name', $data['client_name'])
            ->set('data.client_email', $data['client_email'])
            ->set('data.client_phone', $data['client_phone'])
            ->set('data.client_active', $data['client_active'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', array_merge($data, [
            'client_date_created'  => now()->toDateTimeString(),
            'client_date_modified' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_client(): void
    {
        $client = Client::factory()->create([
            'client_name'  => 'Original Name',
            'client_email' => 'original@example.com',
        ]);

        $updatedData = [
            'client_name'   => 'Updated Name',
            'client_email'  => 'updated@example.com',
            'client_phone'  => '987654321',
            'client_active' => true,
        ];

        Livewire::test(EditClient::class, ['record' => $client->client_id])
            ->set('data.client_name', $updatedData['client_name'])
            ->set('data.client_email', $updatedData['client_email'])
            ->set('data.client_phone', $updatedData['client_phone'])
            ->set('data.client_active', $updatedData['client_active'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clients', array_merge($updatedData, [
            'client_id'            => $client->client_id,
            'client_date_modified' => now()->toDateTimeString(),
        ]));
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_client(): void
    {
        $this->markTestIncomplete('Needs delete action');
        $client = Client::factory()->create([
            'client_name'  => 'Test Client',
            'client_email' => 'client@example.com',
        ]);

        Livewire::test(ManageClients::class)
            ->callTableAction('delete', $client)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('clients', [
            'client_id' => $client->client_id,
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_bulk_deletes_clients(): void
    {
        $clients = Client::factory()->count(3)->create();

        Livewire::test(ManageClients::class)
            ->callTableBulkAction('delete', $clients)
            ->assertHasNoErrors();

        foreach ($clients as $client) {
            $this->assertDatabaseMissing('clients', [
                'client_id' => $client->client_id,
            ]);
        }
    }
}
