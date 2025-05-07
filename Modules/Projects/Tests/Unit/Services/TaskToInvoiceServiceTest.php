<?php

namespace Modules\Projects\Tests\Unit\Services;

use Modules\Projects\Models\Task;
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
