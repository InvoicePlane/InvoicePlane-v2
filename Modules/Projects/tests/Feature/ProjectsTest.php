<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Projects\Filament\Resources\ProjectResource\Pages\CreateProject;
use Modules\Projects\Filament\Resources\ProjectResource\Pages\EditProject;
use Modules\Projects\Filament\Resources\ProjectResource\Pages\ManageProjects;
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
     *
     * @skip Not implemented yet
     */
    public function it_shows_projects_index(): void
    {
        $user = User::factory()->create();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        Livewire::test(ManageProjects::class)
            ->assertStatus(200)
            ->assertSee('::client_name::')
            ->assertSee('::project_name::');
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "client_id": 1,
     *    "project_name": "Project Alpha"
     * }
     */
    public function it_creates_a_project(): void
    {
        $this->markTestSkipped('Something about a view');
        // $this->authenticate();

        $payload = [
            'client_id'    => Client::factory()->create()->client_id,
            'project_name' => '::project_name::',
        ];

        Livewire::test(CreateProject::class)
            ->assertStatus(201)
            ->set('data.client_id', $payload['client_id'])
            ->set('data.project_name', $payload['project_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', $payload);
    }

    /**
     * @test
     *
     * @payload
     * {
     * "client_id": null,
     * "project_name": null
     * }
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_a_project_without_required_fields(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        $payload = [
            'client_id'    => null,
            'project_name' => null,
        ];

        Livewire::test(CreateProject::class)
            ->assertStatus(422)
            ->set('data.client_id', $payload['client_id'])
            ->set('data.project_name', $payload['project_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', $payload);
    }

    /**
     * @test
     *
     * @payload
     * * {
     * *    "project_name": "Updated Project Name"
     * * }
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_project(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $updatedData = [
            'project_name' => '::updated_project_name::',
        ];

        Livewire::test(EditProject::class, ['record' => $project->project_id])
            ->assertStatus(200)
            ->set('data.project_name', $updatedData['project_name'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', array_merge($updatedData, [
            'project_id' => $project->project_id,
        ]));
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "project_id": 1
     * }
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_project(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        $project = Project::factory()->create();

        Livewire::test(ManageProjects::class)
            ->callTableAction('delete', $project->project_id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('projects', ['project_id' => $project->project_id]);
    }
    // endregion

    // region Custom Tests
    /**
     * @test
     *
     * route('filament.ivpl.resources.filament.resources.projects.assign_client')
     *
     * @skip Not implemented yet
     */
    public function it_projects_assign_client(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Client::factory()->create();

        Livewire::test(ManageProjects::class)
            ->assertStatus(200)
            ->callTableAction('assignClient', $client2->client_id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_assign_client_without_project_id(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Client::factory()->create();

        Livewire::test(ManageProjects::class)
            ->assertStatus(422)
            ->callTableAction('assignClient', $client2->client_id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_projects_change_client(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Client::factory()->create();

        Livewire::test(ManageProjects::class)
            ->assertStatus(200)
            ->callTableAction('assignClient', $client2->client_id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_change_project_client_without_client_id(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->client_id]);
        $client2 = Client::factory()->create();

        Livewire::test(ManageProjects::class)
            ->assertStatus(422)
            ->callTableAction('assignClient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'project_id' => $project->project_id,
            'client_id'  => $client2->client_id,
        ]);
    }
    // endregion
}
