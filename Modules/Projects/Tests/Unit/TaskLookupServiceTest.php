<?php

namespace Modules\Projects\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Projects\Models\Task;
use Modules\Projects\Services\TaskLookupService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TaskLookupServiceTest extends AbstractTestCase
{
    /**
     * @payload ["criteria"=>"Urgent"]
     */
    #[Test]
    #[Group('spicy')]
    public function it_finds_tasks_by_criteria(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        Task::factory()->create(['title' => 'Urgent task']);
        $service = new TaskLookupService();
        $results = $service->lookup('Urgent');
        if (app()->isLocal()) {
            dump($results);
        }
        $this->assertNotEmpty($results);
    }

    /**
     * @payload ["criteria"=>""]
     */
    #[Test]
    #[Group('spicy')]
    public function it_returns_empty_for_empty_criteria(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        $service = new TaskLookupService();
        $results = $service->lookup('');
        $this->assertEmpty($results);
    }
}
