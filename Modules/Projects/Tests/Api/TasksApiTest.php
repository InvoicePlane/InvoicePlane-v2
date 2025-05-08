<?php

namespace Modules\Projects\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Clients\Models\Relation;
// use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\ApiTestTrait;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\Group;

class TasksApiTest extends AbstractTestCase
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

    #[Group('crud')]
    public function it_returns_tasks_index(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $client = Relation::factory()->create([
            'client_name' => '::client_name::',
        ]);

        $tax_rate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '9',
        ]);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        Task::factory(5)->create([
            'project_id'  => $project->project_id,
            'task_name'   => '::task_name::',
            'tax_rate_id' => $tax_rate->tax_rate_id,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->get(route('api.tasks.index'));
        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'status',
                    'description',
                    'finishDate',
                    'price',
                    'project',
                    'taxRate',
                ],
            ],
        ]);
        $response->assertJsonFragment(['name' => '::task_name::']);
    }

    #[Group('crud')]
    public function it_creates_a_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $client = Relation::factory()->create([
            'client_name' => '::client_name::',
        ]);

        $tax_rate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '9',
        ]);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $initialTask = Task::factory()->create([
            'project_id'  => $project->project_id,
            'task_name'   => '::task_name::',
            'tax_rate_id' => $tax_rate->tax_rate_id,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.tasks.store'), [
            'project_id'       => $project->project_id,
            'tax_rate_id'      => $tax_rate->tax_rate_id,
            'task_name'        => '::task_name::',
            'task_price'       => '50',
            'task_finish_date' => '2023-12-31',
        ]);

        $response->assertSuccessful();

        $initialTask->refresh();

        $response->assertJsonFragment(['name' => '::project_name::']);
        $response->assertJsonFragment(['name' => '::task_name::']);
    }

    #[Group('crud')]
    public function it_returns_error_when_storing_task_without_proper_fields(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $client = Relation::factory()->create([
            'client_name' => '::client_name::',
        ]);

        $tax_rate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '9',
        ]);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $initialTask = Task::factory()->create([
            'project_id'  => $project->project_id,
            'task_name'   => '::task_name::',
            'tax_rate_id' => $tax_rate->tax_rate_id,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('api.tasks.store'), [
            'client_id' => $client->client_id,
            'task_name' => '::task_name::',
        ]);

        $response->assertStatus(422);

        $initialTask->refresh();

        $response->assertJsonValidationErrorFor('project_id', 'errors');
        $response->assertJsonValidationErrorFor('tax_rate_id', 'errors');
        $response->assertJsonValidationErrorFor('task_price', 'errors');
        $response->assertJsonValidationErrorFor('task_finish_date', 'errors');
    }

    #[Group('crud')]
    public function it_updates_a_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $client = Relation::factory()->create([
            'client_name' => '::client_name::',
        ]);

        $tax_rate = TaxRate::factory()->create([
            'tax_rate_name'    => '::tax_rate_name::',
            'tax_rate_percent' => '9',
        ]);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $initialTask = Task::factory()->create([
            'project_id'  => $project->project_id,
            'task_name'   => '::task_name::',
            'tax_rate_id' => $tax_rate->tax_rate_id,
        ]);

        $updatedData = [
            'task_name' => '::updated_task_name::',
        ];

        $response = $this->put(route('api.tasks.update', ['task' => $initialTask->task_id]), $updatedData);

        $response->assertJsonFragment(['name' => $updatedData['task_name']]);
    }

    public function test_delete_task(): void
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $task = Task::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/tasks/' . $task->task_id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/tasks/' . $task->task_id
        );

        $this->response->assertStatus(404);
    }
}
