<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Clients\Models\Client;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Projects\Filament\Resources\TaskResource\Pages\ManageTasks;
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
     *
     * @skip Not implemented yet
     */
    public function it_shows_tasks_index(): void
    {
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

        Livewire::test(ManageTasks::class)
            ->assertStatus(200)
            ->assertSee('::task_name::')
            ->assertSee('::project_name::');
    }

    /**
     * @test
     *
     * @payload
     * {
     * "project_id": 1,
     * "task_name": "Task Alpha",
     * "task_description": "This is a task description.",
     * "task_price": 100.50,
     * "task_finish_date": "2023-11-20",
     * "task_status": true,
     * "tax_rate_id": 1
     * }
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_task(): void
    {
        // $this->authenticate();

        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'       => $project->project_id,
            'task_name'        => '::task_name::',
            'task_description' => 'This is a task description.',
            'task_price'       => 100.50,
            'task_finish_date' => now()->subDays(5)->format('Y-m-d'),
            'task_status'      => true,
            'tax_rate_id'      => $taxRate->tax_rate_id,
        ];

        Livewire::test(CreateTask::class)
            ->assertStatus(201)
            ->set('data.project_id', $payload['project_id'])
            ->set('data.task_name', $payload['task_name'])
            ->set('data.task_description', $payload['task_description'])
            ->set('data.task_price', $payload['task_price'])
            ->set('data.task_finish_date', $payload['task_finish_date'])
            ->set('data.task_status', $payload['task_status'])
            ->set('data.tax_rate_id', $payload['tax_rate_id'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', $payload);
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "project_id": null,
     *    "task_name": null,
     *    "task_price": null
     * }
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_a_task_without_required_fields(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->project_id,
            'task_name'   => null,
            'tax_rate_id' => $taxRate->tax_rate_id,
        ];

        Livewire::test(CreateTask::class)
            ->assertStatus(201)
            ->set('data.project_id', $payload['project_id'])
            ->set('data.task_name', $payload['task_name'])
            ->set('data.tax_rate_id', $payload['tax_rate_id'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', $payload);
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "task_name": "Updated Task Name"
     * }
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_task(): void
    {
        // $this->authenticate();
        $task = Task::factory()->create(['task_name' => '::task_name::']);

        $updatedData = ['task_name' => '::updated_task_name::'];

        Livewire::test(EditTask::class, ['record' => $task->task_id])
            ->assertStatus(200)
            ->set('data.task_name', $updatedData['task_name'])
            ->set('data.task_status', $updatedData['task_status'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', array_merge($updatedData, [
            'task_id' => $task->task_id,
        ]));
    }

    /**
     * @test
     *
     * @payload
     * {
     *    "task_id": 1
     * }
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_task(): void
    {
        // $this->authenticate();

        $task = Task::factory()->create();

        Livewire::test(ManageTasks::class)
            ->callTableAction('delete', $task->task_id)
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('tasks', ['task_id' => $task->task_id]);
    }

    // endregion

    // region Custom tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_assigns_a_task_to_a_project(): void
    {
        $task = Task::factory()->create();
        $project = Project::factory()->create();

        Livewire::test(ManageTasks::class)
            ->callTableAction('assignProject', $task->task_id, ['project_id' => $project->project_id])
            ->assertStatus(200)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'task_id'    => $task->task_id,
            'project_id' => $project->project_id,
        ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_assign_project_without_project_id(): void
    {
        // $this->authenticate();
        $task = Task::factory()->create();

        Livewire::test(ManageTasks::class)
            ->callTableAction('assignProject', $task->task_id)
            ->assertStatus(422)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'task_id' => $task->task_id,
        ]);
    }

    // endregion

    // region Spicy Functions
    /**
     * @test
     * route('filament.ivpl.resources.filament.resources.projects.store_recurring_task', [
     * 'project_id'       => $project->project_id,
     * 'recur_start_date' => now()->format('Y-m-d'),
     * 'recur_end_date'   => now()->addWeek()->format('Y-m-d'),
     * 'recur_frequency'  => 'weekly', // Ensure this uses the recurring frequency enum
     * ])
     *
     * @skip Not implemented yet
     */
    public function it_creates_recurring_task(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->project_id,
            'task_name'   => null,
            'tax_rate_id' => $taxRate->tax_rate_id,
        ];

        $task = Task::factory()->create($payload);

        Livewire::test(ManageTasks::class)
            ->callTableAction('storeRecurringTask', $task->task_id)
            ->assertStatus(201)
            ->set('data.project_id', $payload['project_id'])
            ->set('data.task_name', $payload['task_name'])
            ->set('data.tax_rate_id', $payload['tax_rate_id'])
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', $payload);
    }

    /**
     * @test
     * route('filament.ivpl.resources.projects.create_recurring_task', [
     * 'project_id'       => $project->project_id,
     * 'recur_start_date' => now()->format('Y-m-d'),
     * ])
     *
     * @skip Not implemented yet
     */
    public function it_fails_to_create_recurring_task_without_frequency(): void
    {
        // $this->authenticate();
        $client = Client::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->project_id,
            'task_name'   => null,
            'tax_rate_id' => $taxRate->tax_rate_id,
        ];

        $task = Task::factory()->create($payload);

        Livewire::test(ManageTasks::class)
            ->callTableAction('storeRecurringTask', $task->task_id)
            ->assertStatus(201)
            ->set('data.project_id', $payload['project_id'])
            ->set('data.task_name', $payload['task_name'])
            ->set('data.tax_rate_id', $payload['tax_rate_id'])
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', $payload);
    }
    // endregion
}
