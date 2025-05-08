<?php

namespace Modules\Projects\Tests\Unit\Services;

use InvalidArgumentException;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Models\Task;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TaskServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_creates_a_task(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $task = (new TaskService())->create(['title' => 'Prepare specs']);
        $this->assertInstanceOf(Task::class, $task);
    }

    #[Test]
    #[Group('services')]
    public function it_requires_title(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->expectException(InvalidArgumentException::class);
        (new TaskService())->create([]);
    }
}
