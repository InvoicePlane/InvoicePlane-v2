<?php

declare(strict_types=1);

namespace Fable\Tests\Unit;

use Fable\Execution\ExecutionGraph;
use Fable\Execution\ExecutionPlanner;
use Fable\Indexer\PRBranchReconciler;
use Fable\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExecutionPlanner::class)]
final class ExecutionPlannerTest extends TestCase
{
    #[Test]
    public function it_plans_execution(): void
    {
        /* Arrange */
        $reconciler = $this->createStub(PRBranchReconciler::class);
        $reconciler->method('build')->willReturn(new ExecutionGraph);

        $planner = new ExecutionPlanner($reconciler);
        $issues = [
            ['id' => '1', 'feature' => 'f1'],
            ['id' => '2', 'feature' => 'f1'],
            ['id' => '3', 'feature' => 'f2'],
        ];

        /* Act */
        $graph = $planner->plan($issues);

        /* Assert */
        $this->assertCount(2, $graph->nodes());
        $this->assertArrayHasKey('feature-f1', $graph->nodes());
        $this->assertArrayHasKey('feature-f2', $graph->nodes());
        $this->assertCount(2, $graph->nodes()['feature-f1']->issues());
        $this->assertCount(1, $graph->nodes()['feature-f2']->issues());
    }
}
