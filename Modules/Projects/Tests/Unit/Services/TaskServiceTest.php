<?php

namespace Modules\Projects\Services;

use Modules\Projects\Models\Task;

use Modules\Projects\Services\TaskService;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_task(): void
    {
        $task = (new TaskService())->create(['title' => 'Prepare specs']);
        $this->assertInstanceOf(Task::class, $task);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_title(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaskService())->create([]);
    }
}
