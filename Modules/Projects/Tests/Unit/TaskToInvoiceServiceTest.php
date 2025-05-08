<?php

namespace Modules\Projects\Tests\Unit;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskToInvoiceService;
use PHPUnit\Framework\Attributes\Test;

class TaskToInvoiceServiceTest extends AbstractTestCase
{
    /**
     * @payload ["taskId"=>$task->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_maps_task_into_invoice_line(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $task    = Task::factory()->create(['hours' => 2, 'rate' => 50]);
        $service = new TaskToInvoiceService();
        $line    = $service->map($task->id);
        if (app()->isLocal()) {
            dump($line);
        }
        $this->assertEquals(100, $line['total']);
    }

    /**
     * @payload ["taskId"=>0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_for_invalid_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $service = new TaskToInvoiceService();
        $this->expectException(Exception::class);
        $service->map(0);
    }
}
