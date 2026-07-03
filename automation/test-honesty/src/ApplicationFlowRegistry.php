<?php

namespace Fable5\Audit;

class ApplicationFlowRegistry
{
    public static function getCriticalFlows(): array
    {
        return [
            'API Communication' => [
                'classes' => ['Fable5\Http\ApiClient'],
                'description' => 'Reliable communication with external APIs with retries and timeouts.'
            ],
            'GitHub Integration' => [
                'classes' => ['Fable5\Clients\GitHubClient', 'Fable5\Clients\GitHubGraphQLClient', 'Fable5\Git\Cli\GitHubCli'],
                'description' => 'Interaction with GitHub for PRs, issues, and repository management.'
            ],
            'Execution Planning' => [
                'classes' => ['Fable5\Execution\ExecutionPlanner'],
                'description' => 'Parsing issues and creating an execution graph.'
            ],
            'Task Scheduling' => [
                'classes' => ['Fable5\Execution\ExecutionScheduler'],
                'description' => 'Ordering tasks and managing concurrency.'
            ],
            'Execution Running' => [
                'classes' => ['Fable5\Execution\ExecutionRunner'],
                'description' => 'Performing the actual git operations and PR creation.'
            ],
        ];
    }
}
