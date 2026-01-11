<?php

namespace Modules\Projects\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
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
        /* Arrange */
        $customer = Customer::factory()->for($this->company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($this->company)
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
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
        /* Arrange */
        $customer = Customer::factory()->for($this->company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($this->company)
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertNotSet('isSaving', true);

        $this->assertDatabaseHas('tasks', array_merge(
            $payload,
            [
                'due_at' => isset($payload['due_at']) ? Carbon::parse($payload['due_at'])->format('Y-m-d H:i:s') : null,
            ]
        ));
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
        /* Arrange */
        $customer = Customer::factory()->for($this->company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($this->company)
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

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasFormErrors(['task_name' => 'required']);

        /* Assert */
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
        /* Arrange */

        $taxRate = TaxRate::factory()
            ->for($this->company)
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
        $customer = Customer::factory()->for($this->company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($this->company)
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
        $customer = Customer::factory()->for($this->company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()
            ->for($customer, 'customer')
            ->for($this->company)
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class, ['record' => $task->getKey()])
            ->mountAction(TestAction::make('edit')->table($task), $updatedData)
            ->fillForm($updatedData)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', array_merge(
            $payload,
            [
                'due_at' => isset($payload['due_at']) ? Carbon::parse($payload['due_at'])->format('Y-m-d H:i:s') : null,
            ]
        ));
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
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */

        $taxRate = TaxRate::factory()
            ->for($this->company)
            ->create(['name' => '::taxrate_name::']);

        $payload = [
            'company_id' => $this->company->id,
            // 'project_id' intentionally omitted
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => $this->user->id,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => '2025-06-01',
            'description' => 'Create a responsive landing page',
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateTask::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditTask::class, ['record' => $task->getKey()])
            ->fillForm($updatedData)
            ->call('save');

        /* Assert */
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
        /* Arrange */
        $customer = Customer::factory()->for($this->company)->create(['company_name' => '::customer_name::']);
        $project  = Project::factory()->for($this->company)->for($customer)->create([
            'project_name' => '::project_name::',
        ]);
        $taxRate = TaxRate::factory()->for($this->company)->create([
            'name' => '::taxrate_name::',
        ]);

        $payload = [
            'tax_rate_id' => $taxRate->id,
            'assigned_to' => null,
            'task_status' => TaskStatus::OPEN->value,
            'task_name'   => 'Design Landing Page',
            'task_price'  => 150.00,
            'due_at'      => now()->addDays(5)->format('Y-m-d'),
            'description' => 'Create a responsive landing page',
        ];

        $task = Task::factory()->for($project)->for($customer)->create($payload);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListTasks::class)
            ->mountAction(TestAction::make('delete')->table($task))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    # endregion
}
