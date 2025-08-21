<?php

namespace Modules\Projects\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Customer;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Filament\Company\Resources\Tasks\Pages\CreateTask;
use Modules\Projects\Filament\Company\Resources\Tasks\Pages\EditTask;
use Modules\Projects\Filament\Company\Resources\Tasks\Pages\ListTasks;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListTasks::class)]
class TasksTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_tasks(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Customer::factory()->for($company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($company)
            ->create([
                'project_name' => '::project_name::',
            ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->id,
            'task_name'   => '::task_name::',
            'customer_id' => $customer->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $this->user->id,
            'task_status' => TaskStatus::OPEN->value,
        ];

        $task = Task::factory()
            ->for($this->company)
            ->for($customer)
            ->for($project)
            ->for($taxRate, 'taxRate')
            ->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$task]);

        $this->assertDatabaseHas('tasks', $payload);
    }
    # endregion

    # region modals
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
    public function it_creates_a_task_through_a_modal(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Customer::factory()->for($company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($company)
            ->create([
                'project_name' => '::project_name::',
            ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->id,
            'customer_id' => $customer->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertNotSet('isSaving', true);

        $this->assertDatabaseHas('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: name
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
    public function it_fails_to_create_task_through_a_modal_without_required_task_name(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Customer::factory()->for($company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($company)
            ->create([
                'project_name' => '::project_name::',
            ]);

        $taxRate = TaxRate::factory()->create(['company_id' => $this->company->id]);

        $payload = [
            'project_id'  => $project->id,
            'customer_id' => $customer->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => TaskStatus::OPEN->value,
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['task_name' => 'required']);

        /* assert */
        $this->assertDatabaseMissing('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: project_id
     * {
     *   "company_id": 1,
     *   "tax_rate_id": 4,
     *   "assigned_to": "john.doe@example.com",
     *   "task_status": "in_progress",
     *   "task_name": "Design Landing Page",
     *   "task_price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    public function it_fails_to_create_task_through_a_modal_without_required_project(): void
    {
        /* arrange */
        $company = $this->user->companies()->first();

        $taxRate = TaxRate::factory()
            ->for($company)
            ->create(['name' => '::taxrate_name::']);

        $payload = [
            // 'project_id' intentionally omitted
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['project_id' => 'required']);
        $this->assertDatabaseMissing('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: tax_rate
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
    public function it_fails_to_create_task_through_a_modal_without_required_tax_rate(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Customer::factory()->for($company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($company)
            ->create([
                'project_name' => '::project_name::',
            ]);

        $payload = [
            'project_id'  => $project->id,
            'customer_id' => $customer->id,
            'assigned_to' => $this->user->id,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['tax_rate_id' => 'required']);

        $this->assertDatabaseMissing('tasks', $payload);
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
     * "task_name": "Example",
     * "price": "9.99",
     * "due_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    public function it_updates_a_task_through_a_modal(): void
    {
        /* arrange */
        $company  = $this->user->companies()->first();
        $customer = Customer::factory()->for($company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($company)
            ->create([
                'project_name' => '::project_name::',
            ]);
        $taxRate = TaxRate::factory()->create(['company_id' => $this->company->id]);

        $task = Task::factory()
            ->for($this->company)
            ->for($customer)
            ->for($project)
            ->for($taxRate, 'taxRate')
            ->create([
                'assigned_to' => $this->user->id,
                'tax_rate_id' => $taxRate->id,
            ]);

        $updatedData = [
            'task_name'   => 'Updated Task Name',
            'task_price'  => 199.99,
            'description' => 'Updated description',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class, ['record' => $task->getKey()])
            ->mountAction(TestAction::make('edit')->table($task), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', array_merge($updatedData, [
            'id' => $task->getKey(),
        ]));
    }
    # endregion

    # region crud
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
    public function it_creates_a_task(): void
    {
        /* arrange */
        $customer = Customer::factory()->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->id,
            'customer_id' => $customer->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: name
     * {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "project_id": 3,
     *   "tax_rate_id": 4,
     *   "assigned_to": null,
     *   "task_status": "in_progress",
     *   "price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    public function it_fails_to_create_task_without_required_name(): void
    {
        /* arrange */
        $customer = Customer::factory()->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'project_id'  => $project->id,
            'customer_id' => $customer->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => TaskStatus::OPEN->value,
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['task_name' => 'required']);

        $this->assertDatabaseMissing('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: project_id
     * {
     *   "company_id": 1,
     *   "tax_rate_id": 4,
     *   "assigned_to": "john.doe@example.com",
     *   "task_status": "in_progress",
     *   "task_name": "Design Landing Page",
     *   "task_price": "150.00",
     *   "due_at": "2025-05-20",
     *   "description": "Create a responsive landing page"
     * }
     */
    public function it_fails_to_create_task_without_required_project(): void
    {
        /* arrange */
        $company = $this->user->companies()->first();

        $taxRate = TaxRate::factory()
            ->for($company)
            ->create(['name' => '::taxrate_name::']);

        $payload = [
            'company_id' => $company->id,
            // 'project_id' intentionally omitted
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $this->user->id,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['project_id' => 'required']);
        $this->assertDatabaseMissing('tasks', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: tax_rate
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
    public function it_fails_to_create_task_without_required_tax_rate(): void
    {
        /* arrange */
        $customer = Customer::factory()->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);

        $payload = [
            'project_id'  => $project->id,
            'customer_id' => $customer->id,
            'assigned_to' => $this->user->id,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['tax_rate_id']);

        $this->assertDatabaseMissing('tasks', $payload);
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
    public function it_updates_a_task(): void
    {
        /* arrange */
        $customer = Relation::factory()->for($this->company)->create([
            'company_name' => '::customer_name::',
        ]);

        $tax_rate = TaxRate::factory()->for($this->company)->create([
            'name' => '::tax_rate_name::',
            'rate' => '9',
        ]);

        $project = Project::factory()->for($this->company)->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->for($this->company)->create();

        $task = Task::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'project_id'  => $project->id,
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $this->user->id,
            'task_status' => TaskStatus::IN_PROGRESS,
            'task_name'   => 'Original Task Name',
            'task_price'  => 199.99,
            'due_at'      => '2025-07-01',
            'description' => 'Original description',
        ]);

        $updatedData = [
            'task_name' => 'Updated Task Name',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditTask::class, ['record' => $task->getKey()])
            ->fillForm($updatedData)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', array_merge($updatedData, [
            'id' => $task->id,
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
     * "task_name": "Example",
     * "price": "9.99",
     * "due_at": "2025-04-30",
     * "description": "Example"
     * }
     */
    public function it_deletes_a_task(): void
    {
        /* arrange */
        $customer = Relation::factory()->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);
        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
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
        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class);
        $component->callAction('delete', $task);
        $component->assertSuccessful()->assertHasNoErrors();
        /* assert */
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_tasks_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s task.');
    }
    # endregion

    # region spicy
    #[Test]
    #[Group('spicy')]
    public function it_assigns_a_task_to_a_project(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $customer = Relation::factory()->create(['company_name' => '::customer_name::']);

        $project = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)->callAction('assignProject', $task->id, ['project_id' => $project->project_id]);

        /* assert */
        $component->assertSuccessful()->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id'         => $task->id,
            'project_id' => $project->project_id,
        ]);
    }

    #[Test]
    #[Group('spicy')]
    public function it_fails_to_assign_project_without_project_id(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $this->markTestIncomplete('assignProject action not implemented');
        // $this->authenticate();
        $customer = Relation::factory()->create(['company_name' => '::customer_name::']);

        $project = Project::factory()->create([
            'customer_id'  => $customer->id,
            'project_name' => '::project_name::',
        ]);

        $taxRate = TaxRate::factory()->create([
            'name' => '::taxrate_name::',
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->callAction('assignProject', $task->id);

        /* assert */
        $component->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }
    # endregion
}
