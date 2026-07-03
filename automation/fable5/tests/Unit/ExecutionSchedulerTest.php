<?php

declare(strict_types=1);

namespace TestHonesty\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TestHonesty\Execution\ExecutionGraph;
use TestHonesty\Execution\ExecutionNode;
use TestHonesty\Execution\ExecutionScheduler;

#[CoversClass(ExecutionScheduler::class)]
final class ExecutionSchedulerTest extends TestCase
{
    #[Test]
    public function it_schedules_tasks(): void
    {
        /* Arrange */
        $graph = new ExecutionGraph;
        $graph->addNode(new ExecutionNode('1', ['issue1']));
        $graph->addNode(new ExecutionNode('2', array_fill(0, 5, 'issue')));
        $graph->addNode(new ExecutionNode('3', array_fill(0, 15, 'issue')));

        $scheduler = new ExecutionScheduler;

        /* Act */
        $batches = $scheduler->schedule($graph);

        /* Assert */
        $this->assertArrayHasKey('small-batch', $batches);
        $this->assertArrayHasKey('medium-batch', $batches);
        $this->assertArrayHasKey('large-batch', $batches);
        $this->assertCount(1, $batches['small-batch']);
        $this->assertCount(1, $batches['medium-batch']);
        $this->assertCount(1, $batches['large-batch']);
    }
}
