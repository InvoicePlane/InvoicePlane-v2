<?php

namespace Modules\Projects\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TaskLookupServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_finds_task_by_id(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $task   = Task::factory()->create();
        $result = TaskLookupService::query()->find($task->id);

        $this->assertEquals($task->id, $result->id);
    }

    #[Test]
    #[Group('services')]
    public function it_returns_null_for_missing_task(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->assertNull(TaskLookupService::query()->find(999999));
    }
}
