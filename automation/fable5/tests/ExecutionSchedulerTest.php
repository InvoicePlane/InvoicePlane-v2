<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Execution\ExecutionGraph;
use Fable5\Execution\ExecutionNode;
use Fable5\Execution\ExecutionScheduler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecutionScheduler::class)]
final class ExecutionSchedulerTest extends TestCase
{
    #[Test]
    public function it_schedules_tasks(): void
    {
        /* Arrange */
        $graph = new ExecutionGraph();
        $graph->addNode(new ExecutionNode('1', 't1'));
        $graph->addNode(new ExecutionNode('2', 't2', [], ['1']));
        $graph->addNode(new ExecutionNode('3', 't3', [], ['1']));
        $graph->addNode(new ExecutionNode('4', 't4', [], ['2', '3']));

        $scheduler = new ExecutionScheduler(maxConcurrency: 2);

        /* Act */
        $plan = $scheduler->schedule($graph);

        /* Assert */
        $this->assertCount(3, $plan);
        $this->assertEquals(['1'], $plan[0]);
        $this->assertEquals(['2', '3'], $plan[1]);
        $this->assertEquals(['4'], $plan[2]);
    }
}
