<?php

declare(strict_types=1);

namespace TestHonesty\Execution;

use TestHonesty\Indexer\PRBranchReconciler;
use TestHonesty\Logging\Logger;

final class Fable5Kernel
{
    public function __construct(
        private Logger $logger,
        private array $config,
        private PRBranchReconciler $reconciler,
        private ExecutionPlanner $planner,
        private ExecutionScheduler $scheduler,
        private ExecutionRunner $runner,
    ) {}

    public function run(): void
    {
        $this->logger->info('Fable5 kernel started');

        $issues = $this->loadIssues();

        if ($this->isEmpty($issues)) {
            $this->logger->info('No issues to process');

            return;
        }

        $this->logger->info('Reconciling issues with existing PRs and branches');
        $reconciledGraph = $this->reconciler->build($issues);

        $this->logger->info('Planning execution strategy');
        // The planner might enrich the graph or build a new one based on dependencies
        // For now, we use the graph from the reconciler as the base
        $graph = $reconciledGraph;

        $this->logger->info('Scheduling tasks');
        $schedule = $this->scheduler->schedule($graph);

        $this->logger->info('Starting execution runner');
        $this->runner->run($graph, $schedule);

        $this->logger->info('Fable5 kernel finished');
    }

    private function loadIssues(): array
    {
        return $this->config['issues'] ?? [];
    }

    private function isEmpty(array $items): bool
    {
        return $items === [];
    }
}
