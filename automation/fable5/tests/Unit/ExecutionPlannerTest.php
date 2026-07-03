<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Execution\ExecutionPlanner;
use Fable5\Indexer\PRBranchReconciler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Modules\Core\Tests\TestCase;

#[CoversClass(ExecutionPlanner::class)]
final class ExecutionPlannerTest extends TestCase
{
    #[Test]
    public function it_plans_execution(): void
    {
        /* Arrange */
        $reconciler = $this->createMock(PRBranchReconciler::class);
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
