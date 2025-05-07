<?php

namespace Modules\Projects\Services;

use Modules\Projects\Models\Task;
use Modules\Projects\Tests\Unit\TaskLookupServiceTest;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaskLookupServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_finds_task_by_id(): void
    {
        $task   = Task::factory()->create();
        $result = TaskLookupService::find($task->id);

        $this->assertEquals($task->id, $result->id);
    }

    #[Test]
    #[Group('services')]
    public function it_returns_null_for_missing_task(): void
    {
        $this->assertNull(TaskLookupService::find(999999));
    }
}
