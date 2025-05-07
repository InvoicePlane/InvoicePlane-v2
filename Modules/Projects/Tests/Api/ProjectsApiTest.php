<?php

namespace Modules\Projects\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;
use Modules\Projects\Models\Project;

class ProjectsApiTest extends AbstractTestCase
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

    public function it_returns_projects_index(): void
    {
        $client = Client::factory()->create([
            'client_name' => '::client_name::',
        ]);

        Project::factory(5)->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.projects.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'client',
                    'name',
                ],
            ],
        ]);
        $response->assertJsonFragment(['name' => '::project_name::']);
    }

    public function it_lists_projects_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $response = $this->getJson(route('api.projects.index'));
        $response->assertSuccessful();
    }

    /**
     * @test
     * Payload for creating a project:
     *
     *
     *            [
     *            'project_name'        => 'Test Project',
     *            'client_id'           => 1,
     *            'project_start_date'  => '2024-01-01',
     *            'project_end_date'    => '2024-12-31',
     *            'project_description' => 'This is a test project',
     *            ]
     */
    public function it_creates_a_project(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $client = Client::factory()->create([
            'client_name' => '::client_name::',
        ]);
        $initialProject = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.projects.store'), [
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $response->assertSuccessful();

        $initialProject->refresh();

        $response->assertJsonFragment(['company' => '::client_name::']);
        $response->assertJsonFragment(['name' => '::project_name::']);
    }

    /**
     * @test
     * Payload for creating a project:
     *
     *
     *            [
     *            'project_name'        => 'Test Project',
     *            'client_id'           => 1,
     *            'project_start_date'  => '2024-01-01',
     *            'project_end_date'    => '2024-12-31',
     *            'project_description' => 'This is a test project',
     *            ]
     */
    public function it_creates_a_project_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $payload = [
            'project_name'        => 'Test Project',
            'client_id'           => 1,
            'project_start_date'  => '2024-01-01',
            'project_end_date'    => '2024-12-31',
            'project_description' => 'This is a test project',
        ];

        $response = $this->postJson(route('api.projects.store'), $payload);
        $response->assertSuccessful();
    }

    public function it_returns_error_response_when_creating_a_project_without_required_field(): void
    {
        $client = Client::factory()->create([
            'client_name' => '::client_name::',
        ]);
        $initialProject = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.projects.store'), [
            'client_id'    => $client->client_id,
            'project_name' => $initialProject->product_name,
        ]);

        $response->assertStatus(422);

        $initialProject->refresh();

        $response->assertJsonMissing(['company' => '::client_name::']);
        $response->assertJsonMissing(['name' => '::project_name::']);
    }

    /**
     * @test
     *
     * Payload for updating a project:
     *
     *
     *            [
     *            'project_name'        => 'Updated Project',
     *            'project_description' => 'Updated description',
     *            ]
     */
    public function it_updates_a_project(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $client = Client::factory()->create([
            'client_name' => '::client_name::',
        ]);

        $initialProject = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $updatedData = [
            'project_name' => '::updated_project_name::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.projects.update', ['project' => $initialProject->project_id]), $updatedData);

        $response->assertSuccessful();

        $initialProject->refresh();

        $response->assertJsonFragment(['name' => $updatedData['project_name']]);
    }

    /**
     * @test
     *
     * Payload for updating a project:
     *
     *
     *            [
     *            'project_name'        => 'Updated Project',
     *            'project_description' => 'Updated description',
     *            ]
     */
    public function it_updates_a_project_via_api(): void
    {
        $payload = [
            'project_name'        => 'Updated Project',
            'project_description' => 'Updated description',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('api.projects.update', ['record' => 1]), $payload);
        $response->assertSuccessful();
    }

    public function it_deletes_a_project_via_api(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $response = $this->deleteJson(route('api.projects.destroy', ['record' => 1]));
        $response->assertSuccessful();
    }
}
