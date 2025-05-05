<?php

namespace Modules\Projects\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Filament\Company\Resources\TaskResource;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\CreateTask;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\EditTask;
use Modules\Projects\Filament\Company\Resources\TaskResource\Pages\ListTasks;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TaskResource::class)]
class TasksTest extends AbstractTestCase
{
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_tasks(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Task::query()->create([
            'company_id' => $company->id,
            'name'       => 'Design Landing Page',
        ]);

        Livewire::test(ListTasks::class)
            ->assertSee('Design Landing Page');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @test
     *
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
        $this->markTestIncomplete('Needs full payload and assertions.');

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'  => $company->id,
            'customer_id' => 2,
            'project_id'  => 3,
            'tax_rate_id' => 4,
            'assigned_to' => 'john.doe@example.com',
            'task_status' => 'in_progress',
            'name'        => 'Design Landing Page',
            'price'       => 150.00,
            'due_at'      => '2025-05-20',
            'description' => 'Create a responsive landing page',
        ];

        Livewire::test(CreateTask::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @test
     *
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
    public function it_fails_to_create_task_without_name(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id' => $company->id,
            'project_id' => 3,
        ];

        Livewire::test(CreateTask::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
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
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Task::factory()->create();

        $payload = [
            'company_id'  => 'Value',
            'customer_id' => 'Value',
            'project_id'  => 'Value',
            'tax_rate_id' => 'Value',
            'assigned_to' => 'Example',
            'task_status' => 'Value',
            'name'        => 'Example',
            'price'       => 9.99,
            'due_at'      => '2025-04-30',
            'description' => 'Example',
        ];

        Livewire::test(EditTask::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
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
    public function it_deletes_a_task(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Task::factory()->create();

        Livewire::test(ListTasks::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('tasks', ['id' => $record->id]);
    }
    // endregion

    // region usp
    // endregion
}
