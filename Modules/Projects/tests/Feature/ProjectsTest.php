<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Projects\Models\Project;

class ProjectsTest extends AbstractTestCase
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
    public function it_shows_projects_index(): void
    {
        $user = User::factory()->create();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.projects.store'));
        $response->assertStatus(200);
        $response->assertSee('::client_name::');
        $response->assertSee('::project_name::');
    }

    /** @test */
    public function it_can_create_a_project(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "client_id": 1,
         *    "project_name": "Project Alpha"
         * }
         */
        $payload = [
            'client_id'    => Client::factory()->create()->client_id,
            'project_name' => 'Project Alpha',
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.store'), $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['project_name' => 'Project Alpha']);
    }

    /** @test */
    public function it_fails_to_create_a_project_without_required_fields(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "client_id": null,
         *    "project_name": null
         * }
         */
        $payload = [
            'client_id'    => null,
            'project_name' => null,
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.store'), $payload);

        // Assert
        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function it_can_update_a_project(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "project_name": "Updated Project Name"
         * }
         */
        $project = Project::factory()->create();

        $payload = [
            'project_name' => 'Updated Project Name',
        ];

        // Act
        $response = $this->put(route('filament.ivpl.resources.filament.resources.projects.update', $project->project_id), $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['project_id' => $project->project_id, 'project_name' => 'Updated Project Name']);
    }

    /** @test */
    public function it_can_delete_a_project(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "project_id": 1
         * }
         */
        $project = Project::factory()->create();

        // Act
        $response = $this->delete(route('filament.ivpl.resources.filament.resources.projects.destroy', $project->project_id));

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', ['project_id' => $project->project_id]);
    }
    // endregion

    // region Custom Tests
    /** @test */
    public function it_projects_assign_client(): void
    {
        // $this->authenticate();
        $project = Project::factory()->create();
        $client = Client::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.assign_client'), [
            'project_id' => $project->project_id,
            'client_id'  => $client->client_id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client->client_id,
        ]);
    }

    /** @test */
    public function it_fails_to_assign_client_without_project_id(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.assign_client'), [
            'client_id' => $client->client_id,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_projects_change_client(): void
    {
        $project = Project::factory()->create();
        $client = Client::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.change_client'), [
            'project_id' => $project->project_id,
            'client_id'  => $client->client_id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client->client_id,
        ]);
    }

    /** @test */
    public function it_fails_to_change_project_client_without_client_id(): void
    {
        $project = Project::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.change_client'), [
            'project_id' => $project->project_id,
        ]);

        $response->assertStatus(422);
    }
}
