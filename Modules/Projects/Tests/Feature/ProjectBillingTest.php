<?php

namespace Modules\Projects\Tests\Feature;

use InvalidArgumentException;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Projects\Enums\TaskStatus;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\ProjectBillingService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ProjectBillingTest extends AbstractCompanyPanelTestCase
{
    private ProjectBillingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user);
        $this->service = app(ProjectBillingService::class);
    }

    #[Test]
    #[Group('crud')]
    public function it_bills_completed_tasks_to_a_new_draft_invoice(): void
    {
        /* Arrange */
        $project = $this->createProject();
        $taskA   = $this->createTask($project, ['task_name' => 'Design', 'task_price' => 100]);
        $taskB   = $this->createTask($project, ['task_name' => 'Build', 'task_price' => 250.5]);

        /* Act */
        $invoice = $this->service->billTasks($project, [$taskA->id, $taskB->id]);

        /* Assert */
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->invoice_status);
        $this->assertSame($project->customer_id, $invoice->customer_id);
        $this->assertNull($invoice->invoice_number);
        $this->assertCount(2, $invoice->invoiceItems);
        $this->assertEqualsWithDelta(350.5, (float) $invoice->invoice_item_subtotal, 0.001);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'task_id'    => $taskA->id,
            'item_name'  => 'Design',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_appends_tasks_to_an_existing_draft_invoice(): void
    {
        /* Arrange */
        $project = $this->createProject();
        $taskA   = $this->createTask($project, ['task_price' => 100]);
        $taskB   = $this->createTask($project, ['task_price' => 50]);

        $first = $this->service->billTasks($project, [$taskA->id]);

        /* Act */
        $second = $this->service->billTasks($project, [$taskB->id]);

        /* Assert */
        $this->assertSame($first->id, $second->id);
        $this->assertCount(2, $second->invoiceItems);
        $this->assertSame(1, Invoice::query()->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_does_not_bill_the_same_task_twice(): void
    {
        /* Arrange */
        $project = $this->createProject();
        $task    = $this->createTask($project, ['task_price' => 100]);

        $this->service->billTasks($project, [$task->id]);

        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->service->billTasks($project, [$task->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_rejects_tasks_that_are_not_completed(): void
    {
        /* Arrange */
        $project = $this->createProject();
        $task    = $this->createTask($project, ['task_status' => TaskStatus::IN_PROGRESS->value]);

        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->service->billTasks($project, [$task->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_only_offers_completed_unbilled_tasks_as_options(): void
    {
        /* Arrange */
        $project   = $this->createProject();
        $completed = $this->createTask($project, ['task_name' => 'Done']);
        $open      = $this->createTask($project, [
            'task_name'   => 'Open',
            'task_status' => TaskStatus::OPEN->value,
        ]);
        $billed = $this->createTask($project, ['task_name' => 'Billed']);
        $this->service->billTasks($project, [$billed->id]);

        /* Act */
        $options = $this->service->billableTaskOptions($project);

        /* Assert */
        $this->assertSame([$completed->id => 'Done'], $options);
    }

    private function createProject(): Project
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();

        return Project::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
        ]);
    }

    private function createTask(Project $project, array $attributes = []): Task
    {
        return Task::factory()->for($this->company)->create(array_merge([
            'customer_id' => $project->customer_id,
            'project_id'  => $project->id,
            'task_status' => TaskStatus::COMPLETED->value,
            'task_price'  => 100,
        ], $attributes));
    }
}
