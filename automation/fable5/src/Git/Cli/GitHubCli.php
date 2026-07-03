<?php

declare(strict_types=1);

namespace Fable5\Git\Cli;

use Exception;
use Fable5\Logging\Logger;
use Illuminate\Support\Facades\Process;

final class GitHubCli
{
    private const int MAX_RETRIES = 3;

    private const int BACKOFF_MULTIPLIER = 2;

    private const int INITIAL_DELAY_MS = 500;

    private const int PAGE_DELAY_MS = 100;

    public function __construct(
        private readonly Logger $logger,
        private readonly string $githubToken,
        private readonly string $ghBinary = 'gh'
    ) {}

    public function listFailedWorkflows(string $repo, int $page = 1, int $perPage = 30): array
    {
        return $this->execute([
            'api',
            "repos/{$repo}/actions/runs",
            '-F', 'status=failure',
            '-F', "per_page={$perPage}",
            '-F', "page={$page}",
        ]);
    }

    public function listAllFailedWorkflows(string $repo, int $maxPages = 1000): array
    {
        $allRuns = [];
        $perPage = 100;

        for ($page = 1; $page <= $maxPages; $page++) {
            $response = $this->execute([
                'api',
                "repos/{$repo}/actions/runs",
                '-F', 'status=failure',
                '-F', "per_page={$perPage}",
                '-F', "page={$page}",
            ]);

            $batch = $response['workflow_runs'] ?? [];

            if (empty($batch)) {
                break;
            }

            $allRuns = array_merge($allRuns, $batch);

            if (count($batch) < $perPage) {
                break;
            }

            usleep(self::PAGE_DELAY_MS * 1000);
        }

        return $allRuns;
    }

    public function rerunWorkflowRun(int $runId): array
    {
        return $this->execute(['run', 'rerun', (string) $runId]);
    }

    public function rerunFailedJobs(int $runId): array
    {
        return $this->execute(['run', 'rerun', (string) $runId, '--failed']);
    }

    public function getWorkflowRunLogs(int $runId): string
    {
        // Logs are usually text/binary, not JSON
        $command = array_merge([$this->ghBinary], ['run', 'view', (string) $runId, '--log']);
        $result  = Process::withEnvironmentVariables([
            'GH_TOKEN'     => $this->githubToken,
            'GITHUB_TOKEN' => $this->githubToken,
            'NO_COLOR'     => '1',
        ])->run($command);

        if ( ! $result->successful()) {
            throw new Exception("Failed to get logs for run {$runId}: " . $result->errorOutput());
        }

        return $result->output();
    }

    public function listWorkflowRuns(string $repo, string $status = 'failed'): array
    {
        return $this->execute([
            'api',
            "repos/{$repo}/actions/runs",
            '-F', "status={$status}",
        ]);
    }

    public function deleteWorkflowRun(string $repo, int $runId): bool
    {
        try {
            $this->execute(['api', '-X', 'DELETE', "repos/{$repo}/actions/runs/{$runId}"]);

            return true;
        } catch (Exception $e) {
            $this->logger->error("Failed to delete workflow run {$runId} in {$repo}: " . $e->getMessage());

            return false;
        }
    }

    // --- Issues ---

    public function listIssues(string $repo, array $args = []): array
    {
        $command = ['issue', 'list', '-R', $repo, '--json', 'number,title,state,author,createdAt'];
        foreach ($args as $key => $value) {
            $command[] = "--{$key}";
            if ($value !== true) {
                $command[] = $value;
            }
        }

        return $this->execute($command);
    }

    public function createIssue(string $repo, string $title, string $body, array $labels = []): array
    {
        $command = ['issue', 'create', '-R', $repo, '-t', $title, '-b', $body];
        foreach ($labels as $label) {
            $command[] = '-l';
            $command[] = $label;
        }

        return $this->execute($command);
    }

    // --- Pull Requests ---

    public function listPullRequests(string $repo, array $args = []): array
    {
        $command = ['pr', 'list', '-R', $repo, '--json', 'number,title,state,author,headRefName,baseRefName'];
        foreach ($args as $key => $value) {
            $command[] = "--{$key}";
            if ($value !== true) {
                $command[] = $value;
            }
        }

        return $this->execute($command);
    }

    public function createPullRequest(string $repo, string $title, string $body, string $base = 'main', string $head = ''): array
    {
        $command = ['pr', 'create', '-R', $repo, '-t', $title, '-b', $body, '-B', $base];
        if ($head) {
            $command[] = '-H';
            $command[] = $head;
        }

        return $this->execute($command);
    }

    public function mergePullRequest(string $repo, int $number, string $method = 'squash'): bool
    {
        try {
            $this->execute(['pr', 'merge', '-R', $repo, (string) $number, "--{$method}", '--delete-branch']);

            return true;
        } catch (Exception $e) {
            $this->logger->error("Failed to merge PR {$number} in {$repo}: " . $e->getMessage());

            return false;
        }
    }

    // --- Projects ---

    public function listProjects(string $owner): array
    {
        return $this->execute(['project', 'list', '--owner', $owner, '--json', 'number,title,id,url']);
    }

    public function viewProject(int $number, string $owner): array
    {
        return $this->execute(['project', 'view', (string) $number, '--owner', $owner, '--json', 'number,title,items,id']);
    }

    /**
     * Delete ALL workflow runs in a repository with optional status filter.
     * Handles 1000's of runs by iterating and deleting.
     */
    public function deleteAllWorkflowRuns(string $repo, ?string $status = null, int $maxRuns = 100000): int
    {
        $deletedCount = 0;
        $perPage      = 100;

        while ($deletedCount < $maxRuns) {
            $query = [
                'api',
                "repos/{$repo}/actions/runs",
                '-F', "per_page={$perPage}",
            ];

            if ($status) {
                $query[] = '-F';
                $query[] = "status={$status}";
            }

            $response = $this->execute($query);
            $runs     = $response['workflow_runs'] ?? [];

            if (empty($runs)) {
                break;
            }

            foreach ($runs as $run) {
                if ($this->deleteWorkflowRun($repo, (int) $run['id'])) {
                    $deletedCount++;
                }

                if ($deletedCount >= $maxRuns) {
                    break;
                }
            }

            // GitHub Actions API sometimes takes a moment to reflect deletions in listing,
            // but usually it's fine to just fetch the "next" first page again since the previous ones are gone.
            usleep(self::PAGE_DELAY_MS * 1000);
        }

        $this->logger->info("Bulk deletion finished. Total runs deleted in {$repo}: {$deletedCount}");

        return $deletedCount;
    }

    private function execute(array $args): array
    {
        $attempt = 0;
        $delay   = self::INITIAL_DELAY_MS;
        $command = array_merge([$this->ghBinary], $args);

        while ($attempt < self::MAX_RETRIES) {
            try {
                $result = Process::env([
                    'GH_TOKEN'     => $this->githubToken,
                    'GITHUB_TOKEN' => $this->githubToken,
                    'NO_COLOR'     => '1',
                ])->run(implode(' ', array_map('escapeshellarg', $command)));

                if ($result->successful()) {
                    $output = $result->output();
                    if (empty($output)) {
                        return [];
                    }
                    $decoded = json_decode($output, true);

                    return is_array($decoded) ? $decoded : [$output];
                }

                $error = $result->errorOutput();
                $this->logger->error('GH CLI Error (Attempt ' . ($attempt + 1) . '): ' . $error, [
                    'args'     => $args,
                    'exitCode' => $result->exitCode(),
                ]);
            } catch (Exception $e) {
                $this->logger->error('GH CLI Exception (Attempt ' . ($attempt + 1) . '): ' . $e->getMessage(), [
                    'args' => $args,
                ]);
            }

            $attempt++;
            if ($attempt < self::MAX_RETRIES) {
                usleep($delay * 1000);
                $delay *= self::BACKOFF_MULTIPLIER;
            }
        }

        throw new Exception('Failed to execute GH CLI command after ' . self::MAX_RETRIES . ' attempts.');
    }
}
