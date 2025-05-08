<?php

namespace Modules\Projects\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TaskToInvoiceServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_generates_invoice_from_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $task    = Task::factory()->create();
        $invoice = (new TaskToInvoiceService())->convert($task);

        $this->assertNotNull($invoice->id);
    }
}
