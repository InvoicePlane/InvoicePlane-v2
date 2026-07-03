<?php

declare(strict_types=1);

namespace Fable5\Clients;

use Fable5\Http\ApiClient;
use Fable5\Http\HttpMethod;
use Generator;

final class GitHubClient
{
    public function __construct(
        private ApiClient $transport
    ) {}

    public function getRepository(string $owner, string $repo): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}")->json();
    }

    public function createPullRequest(string $owner, string $repo, array $data): array
    {
        return $this->transport->request(HttpMethod::POST, "https://api.github.com/repos/{$owner}/{$repo}/pulls", $data)->json();
    }

    public function getPullRequest(string $owner, string $repo, int $number): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/pulls/{$number}")->json();
    }

    public function listPullRequests(string $owner, string $repo, array $query = []): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/pulls", $query)->json();
    }

    // --- Issues ---

    public function createIssue(string $owner, string $repo, array $data): array
    {
        return $this->transport->request(HttpMethod::POST, "https://api.github.com/repos/{$owner}/{$repo}/issues", $data)->json();
    }

    public function getIssue(string $owner, string $repo, int $number): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/issues/{$number}")->json();
    }

    public function updateIssue(string $owner, string $repo, int $number, array $data): array
    {
        return $this->transport->request(HttpMethod::PATCH, "https://api.github.com/repos/{$owner}/{$repo}/issues/{$number}", $data)->json();
    }

    public function listIssues(string $owner, string $repo, array $query = []): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/issues", $query)->json();
    }

    public function addIssueComment(string $owner, string $repo, int $issueNumber, string $body): array
    {
        return $this->transport->request(HttpMethod::POST, "https://api.github.com/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments", ['body' => $body])->json();
    }

    // --- Repository Management ---

    public function updateRepository(string $owner, string $repo, array $data): array
    {
        return $this->transport->request(HttpMethod::PATCH, "https://api.github.com/repos/{$owner}/{$repo}", $data)->json();
    }

    public function deleteRepository(string $owner, string $repo): bool
    {
        return $this->transport->request(HttpMethod::DELETE, "https://api.github.com/repos/{$owner}/{$repo}")->successful();
    }

    public function listRepositoryTopics(string $owner, string $repo): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/topics")->json();
    }

    public function replaceRepositoryTopics(string $owner, string $repo, array $names): array
    {
        return $this->transport->request(HttpMethod::PUT, "https://api.github.com/repos/{$owner}/{$repo}/topics", ['names' => $names])->json();
    }

    /**
     * @return Generator<int, array>
     */
    public function listWorkflowRuns(string $owner, string $repo, ?string $status = null): Generator
    {
        $page = 1;
        $perPage = 100;

        while (true) {
            $query = [
                'per_page' => $perPage,
                'page' => $page,
            ];

            if ($status !== null) {
                $query['status'] = $status;
            }

            $response = $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs", $query);
            $data = $response->json();

            $runs = $data['workflow_runs'] ?? [];

            if (empty($runs)) {
                break;
            }

            foreach ($runs as $run) {
                yield $run;
            }

            if (count($runs) < $perPage) {
                break;
            }

            $page++;
        }
    }

    public function getWorkflowRun(string $owner, string $repo, int $runId): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}")->json();
    }

    /**
     * @return Generator<int, array>
     */
    public function listFailedWorkflowRuns(string $owner, string $repo): Generator
    {
        return $this->listWorkflowRuns($owner, $repo, 'failure');
    }

    public function listWorkflowJobs(string $owner, string $repo, int $runId): array
    {
        return $this->transport->request(HttpMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}/jobs")->json();
    }

    public function deleteWorkflowRun(string $owner, string $repo, int $runId): bool
    {
        return $this->transport->request(HttpMethod::DELETE, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}")->successful();
    }


    public function branchExists(string $owner, string $repo, string $branch): bool
    {
        return false;
    }

    public function createBranch(string $owner, string $repo, string $branch): void
    {
    }
}
