<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Clients\Models\Client;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;

class TasksTest extends AbstractTestCase
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

    // region CRUD tests

    /**
     * @test
     */
    public function it_shows_tasks_index(): void
    {
        $this->markTestSkipped('Not implemented yet');

        $user = User::factory()->create();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        Task::factory()->create([
            'project_id'  => $project->project_id,
            'task_name'   => '::task_name::',
            'tax_rate_id' => $taxRate->tax_rate_id,
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.tasks.index'));
        $response->assertStatus(200);
        $response->assertSee('::task_name::');
        $response->assertSee('::project_name::');
    }

    /** @test */
    public function it_can_create_a_task(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "project_id": 1,
         *    "task_name": "Task Alpha",
         *    "task_description": "This is a task description.",
         *    "task_price": 100.50,
         *    "task_finish_date": "2023-11-20",
         *    "task_status": true,
         *    "tax_rate_id": 1
         * }
         */
        $payload = [
            'project_id'       => Project::factory()->create()->project_id,
            'task_name'        => 'Task Alpha',
            'task_description' => 'This is a task description.',
            'task_price'       => 100.50,
            'task_finish_date' => now()->subDays(5)->format('Y-m-d'),
            'task_status'      => true,
            'tax_rate_id'      => TaxRate::factory()->create()->tax_rate_id,
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.tasks.store'), $payload);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['task_name' => 'Task Alpha']);
    }

    /** @test */
    public function it_fails_to_create_a_task_without_required_fields(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "project_id": null,
         *    "task_name": null,
         *    "task_price": null
         * }
         */
        $payload = [
            'task_name'  => null,
            'project_id' => Project::factory()->create()->project_id,
        ];

        // Act
        $response = $this->post(route('filament.ivpl.resources.filament.resources.tasks.store'), $payload);

        // Assert
        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['task_name']);
    }

    /** @test */
    public function it_can_update_a_task(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "task_name": "Updated Task Name"
         * }
         */
        $task = Task::factory()->create();

        $payload = [
            'task_name' => 'Updated Task Name',
        ];

        // Act
        $response = $this->put(route('filament.ivpl.resources.filament.resources.tasks.update', $task->task_id), $payload);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['task_id' => $task->task_id, 'task_name' => 'Updated Task Name']);
    }

    /** @test */
    public function it_can_delete_a_task(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticate();

        /**
         * @payload
         * {
         *    "task_id": 1
         * }
         */
        $task = Task::factory()->create();

        // Act
        $response = $this->delete(route('filament.ivpl.resources.filament.resources.tasks.destroy', $task->task_id));

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['task_id' => $task->task_id]);
    }

    // endregion

    // region Custom tests

    /** @test */
    public function it_tasks_assign_project(): void
    {
        // $this->authenticate();
        $task = Task::factory()->create();
        $project = Project::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.tasks.assign_project'), [
            'task_id'    => $task->task_id,
            'project_id' => $project->project_id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'task_id'    => $task->task_id,
            'project_id' => $project->project_id,
        ]);
    }

    /** @test */
    public function it_fails_to_assign_project_without_project_id(): void
    {
        // $this->authenticate();
        $task = Task::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.tasks.assign_project'), [
            'task_id' => $task->task_id,
        ]);

        $response->assertStatus(422);
    }

    // endregion

    // region Spicy Functions
    /** @test */
    public function it_projects_create_recurring_task(): void
    {
        // $this->authenticate();
        $project = Project::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.filament.resources.projects.store_recurring_task', [
            'project_id'       => $project->project_id,
            'recur_start_date' => now()->format('Y-m-d'),
            'recur_end_date'   => now()->addWeek()->format('Y-m-d'),
            'recur_frequency'  => 'weekly', // Ensure this uses the recurring frequency enum
        ]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('recurring_tasks', [
            'project_id'      => $project->project_id,
            'recur_frequency' => 'weekly',
        ]);
    }

    /** @test */
    public function it_fails_to_create_recurring_task_without_frequency(): void
    {
        // $this->authenticate();
        $project = Project::factory()->create();

        $response = $this->post(route('filament.ivpl.resources.projects.create_recurring_task', [
            'project_id'       => $project->project_id,
            'recur_start_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(422);
    }
}
