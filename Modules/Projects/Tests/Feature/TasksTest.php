<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Filament\Company\Resources\TaskResource;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\CreateTask;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\EditTask;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\ListTasks;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TaskResource::class)]
class TasksTest extends AbstractTestCase
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

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_tasks(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $client = Relation::factory()->create(['client_name' => '::client_name::']);

        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'tax_rate_name' => '::taxrate_name::',
        ]);

        //$taxRate = TaxRate::factory()->for($company)->create();

        $task = Task::factory()
            ->for($company)
            ->for($taxRate, 'taxRate')
            ->create([
                'project_id' => $project->project_id,
                'task_name'  => '::task_name::',
            ]);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListTasks::class);

        /* assert */
        $component->assertSuccessful()->assertCanSeeTableRecords([$task]);
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_id": 3,
     *   "tax_rate_id": 4,
     *   "assigned_to": "john.doe@example.com",
     *   "task_status": "in_progress",
     *   "name": "Design Landing Page",
     *   "price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    #[Group('crud')]
    public function it_creates_a_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        TaxRate::factory()->create(['company_id' => $company->id]);

        $payload = [
            'company_id'  => $company->id,
            'customer_id' => 1,
            'project_id'  => 1,
            'tax_rate_id' => 1,
            'assigned_to' => $user->id,
            'task_status' => TaskStatus::OPEN,
            'name'        => 'Design Landing Page',
            'price'       => 150.00,
            'due_at'      => now()->subDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateTask::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_id": 3,
     *   "tax_rate_id": 4,
     *   "assigned_to": "john.doe@example.com",
     *   "task_status": "in_progress",
     *   "price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    #[Group('crud')]
    public function it_fails_to_create_task_without_required_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $client  = Relation::factory()->create(['client_name' => '::client_name::']);
        $project = Project::factory()->create([
            'client_id'    => $client->client_id,
            'project_name' => '::project_name::',
        ]);

        TaxRate::factory()->create(['company_id' => $company->id]);

        $payload = [
            'company_id'  => $company->id,
            'customer_id' => 1,
            'project_id'  => 1,
            'tax_rate_id' => 1,
            'assigned_to' => $user->id,
            'task_status' => TaskStatus::OPEN,
            'price'       => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateTask::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['data.task_name' => 'required']);

        $this->assertDatabaseMissing('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "project_id": 3,
     *   "tax_rate_id": 4,
     *   "assigned_to": "john.doe@example.com",
     *   "task_status": "in_progress",
     *   "price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    #[Group('crud')]
    public function it_fails_to_create_task_without_required_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        TaxRate::factory()->create(['company_id' => $company->id]);

        $payload = [
            'company_id'  => $company->id,
            'project_id'  => 1,
            'tax_rate_id' => 1,
            'assigned_to' => $user->id,
            'task_status' => TaskStatus::OPEN,
            'name'        => 'Design Landing Page',
            'price'       => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        $task = Task::factory()->create($payload);

        $updatedData = ['task_name' => '::updated_task_name::'];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateTask::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_id": 3,
     *   "tax_rate_id": 4,
     *   "task_status": "in_progress",
     *   "price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    #[Group('crud')]
    public function it_fails_to_create_task_without_required_assigned_to(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        TaxRate::factory()->create(['company_id' => $company->id]);

        $payload = [
            'company_id'  => $company->id,
            'customer_id' => 1,
            'project_id'  => 1,
            'tax_rate_id' => 1,
            'task_status' => TaskStatus::OPEN,
            'name'        => 'Design Landing Page',
            'price'       => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateTask::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['assigned_to']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_id": 3,
     *   "assigned_to": "john.doe@example.com",
     *   "task_status": "in_progress",
     *   "price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    #[Group('crud')]
    public function it_fails_to_create_task_without_required_tax_rate(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'  => $company->id,
            'customer_id' => 1,
            'project_id'  => 1,
            'assigned_to' => $user->id,
            'task_status' => TaskStatus::OPEN,
            'name'        => 'Design Landing Page',
            'price'       => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateTask::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['tax_rate_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "customer_id": "Value",
     * "project_id": "Value",
     * "tax_rate_id": "Value",
     * "assigned_to": "Example",
     * "task_status": "Value",
     * "name": "Example",
     * "price": "9.99",
     * "due_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    #[Group('crud')]
    public function it_updates_a_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        $this->actingAs($user);

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

        $taxRate = TaxRate::factory()->create(['company_id' => $company->id]);

        $task = Task::factory()->create([
            'company_id'  => $company->id,
            'assigned_to' => $user->id,
            'tax_rate_id' => $taxRate->id,
        ]);

        $payload = [
            'company_id'  => $company->id,
            'customer_id' => $task->customer_id,
            'project_id'  => $task->project_id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $user->id,
            'task_status' => TaskStatus::IN_PROGRESS,
            'name'        => 'Updated Task Name',
            'price'       => 199.99,
            'due_at'      => '2025-07-01',
            'description' => 'Updated description',
        ];

        /** act */
        $component = Livewire::actingAs($this->user)->test(EditTask::class, ['record' => $task->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        $this->assertDatabaseHas('tasks', array_merge($updatedData, [
            'task_id' => $task->task_id,
        ]));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "customer_id": "Value",
     * "project_id": "Value",
     * "tax_rate_id": "Value",
     * "assigned_to": "Example",
     * "task_status": "Value",
     * "name": "Example",
     * "price": "9.99",
     * "due_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    #[Group('crud')]
    public function it_deletes_a_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('delete action not implemented');
        // $this->authenticate();
        $client = Relation::factory()->create(['client_name' => '::client_name::']);

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

        $task = Task::factory()->create($payload);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListTasks::class)->callTableAction('delete', $task->task_id);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseMissing('tasks', ['task_id' => $task->task_id]);
    }

    // endregion

    // region Custom tests
    #[Group('crud')]
    public function it_assigns_a_task_to_a_project(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('assignProject action not implemented');
        $client = Relation::factory()->create(['client_name' => '::client_name::']);

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

        $task = Task::factory()->create($payload);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListTasks::class)->callTableAction('assignProject', $task->task_id, ['project_id' => $project->project_id]);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'task_id'    => $task->task_id,
            'project_id' => $project->project_id,
        ]);
    }

    #[Group('crud')]
    public function it_fails_to_assign_project_without_project_id(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('assignProject action not implemented');
        // $this->authenticate();
        $client = Relation::factory()->create(['client_name' => '::client_name::']);

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

        $task = Task::factory()->create($payload);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListTasks::class)->callTableAction('assignProject', $task->task_id);

        /* assert */
        $component->assertStatus(422)->assertHasNoErrors();

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
    #[Group('crud')]
    public function it_creates_recurring_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // $this->authenticate();
        $client = Relation::factory()->create(['client_name' => '::client_name::']);

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

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListTasks::class)->callTableAction('storeRecurringTask', $task->task_id)->set('data.project_id', $payload['project_id'])->set('data.task_name', $payload['task_name'])->set('data.tax_rate_id', $payload['tax_rate_id']);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

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
    #[Group('crud')]
    public function it_fails_to_create_recurring_task_without_frequency(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        // $this->authenticate();
        $client = Relation::factory()->create(['client_name' => '::client_name::']);

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

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListTasks::class)->callTableAction('storeRecurringTask', $task->task_id)->set('data.project_id', $payload['project_id'])->set('data.task_name', $payload['task_name'])->set('data.tax_rate_id', $payload['tax_rate_id']);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', $payload);
    }
    // endregion

    // region usp
    // endregion
}
