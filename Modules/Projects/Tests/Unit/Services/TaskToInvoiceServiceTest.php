<?php

namespace Modules\Projects\Services;

use Modules\Projects\Tests\Unit\TaskToInvoiceServiceTest;

use Modules\Projects\Models\Task;

use Modules\Projects\Services\TaskToInvoiceService;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaskToInvoiceServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_generates_invoice_from_task(): void
    {
        $task    = Task::factory()->create();
        $invoice = (new TaskToInvoiceService())->convert($task);

        $this->assertNotNull($invoice->id);
    }
}
