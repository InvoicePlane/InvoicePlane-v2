<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Execution\ExecutionPlanner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecutionPlanner::class)]
final class ExecutionPlannerTest extends TestCase
{
    #[Test]
    public function it_builds_graph(): void
    {
        /* Arrange */
        $planner = new ExecutionPlanner();
        $tasks = [
            ['id' => '1', 'type' => 't1', 'dependencies' => []],
            ['id' => '2', 'type' => 't2', 'dependencies' => ['1']],
        ];

        /* Act */
        $graph = $planner->buildGraph($tasks);

        /* Assert */
        $this->assertCount(2, $graph->getNodes());
        $this->assertCount(1, $graph->getRoots());
        $this->assertEquals('1', $graph->getRoots()['1']->getId());
    }
}
