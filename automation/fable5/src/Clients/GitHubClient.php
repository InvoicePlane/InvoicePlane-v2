<?php

declare(strict_types=1);

namespace Fable\Clients;

use Fable\Http\ApiClient;
use Fable\Http\RequestMethod;
use Generator;
use Illuminate\Http\Client\Response;

final class GitHubClient
{
    public function __construct(
        private ApiClient $transport,
        private string $token,
    ) {}

    /** @return array<string, mixed> */
    public function getRepository(string $owner, string $repo): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}")->json();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createPullRequest(string $owner, string $repo, array $data): array
    {
        return $this->request(RequestMethod::POST, "https://api.github.com/repos/{$owner}/{$repo}/pulls", $data)->json();
    }

    public function log(string $message): void
    {
        // Internal logging or console output could go here
    }

    /** @return array<string, mixed> */
    public function getPullRequest(string $owner, string $repo, int $number): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/pulls/{$number}")->json();
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function listPullRequests(string $owner, string $repo, array $query = []): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/pulls", $query)->json();
    }

    // --- Issues ---

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createIssue(string $owner, string $repo, array $data): array
    {
        return $this->request(RequestMethod::POST, "https://api.github.com/repos/{$owner}/{$repo}/issues", $data)->json();
    }

    /** @return array<string, mixed> */
    public function getIssue(string $owner, string $repo, int $number): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/issues/{$number}")->json();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateIssue(string $owner, string $repo, int $number, array $data): array
    {
        return $this->request(RequestMethod::PATCH, "https://api.github.com/repos/{$owner}/{$repo}/issues/{$number}", $data)->json();
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function listIssues(string $owner, string $repo, array $query = []): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/issues", $query)->json();
    }

    /** @return array<string, mixed> */
    public function addIssueComment(string $owner, string $repo, int $issueNumber, string $body): array
    {
        return $this->request(RequestMethod::POST, "https://api.github.com/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments", ['body' => $body])->json();
    }

    // --- Repository Management ---

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateRepository(string $owner, string $repo, array $data): array
    {
        return $this->request(RequestMethod::PATCH, "https://api.github.com/repos/{$owner}/{$repo}", $data)->json();
    }

    public function deleteRepository(string $owner, string $repo): bool
    {
        return $this->request(RequestMethod::DELETE, "https://api.github.com/repos/{$owner}/{$repo}")->successful();
    }

    /** @return array<int, string> */
    public function listRepositoryTopics(string $owner, string $repo): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/topics")->json();
    }

    /**
     * @param  array<int, string>  $names
     * @return array<string, mixed>
     */
    public function replaceRepositoryTopics(string $owner, string $repo, array $names): array
    {
        return $this->request(RequestMethod::PUT, "https://api.github.com/repos/{$owner}/{$repo}/topics", ['names' => $names])->json();
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function listWorkflowRuns(string $owner, string $repo, ?string $status = null): Generator
    {
        $page = 1;
        $perPage = 100;
        $maxPages = 10; // Safety cap

        while ($page <= $maxPages) {
            $query = [
                'per_page' => $perPage,
                'page' => $page,
            ];

            if ($status !== null) {
                $query['status'] = $status;
            }

            $response = $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs", $query);
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

    /** @return array<string, mixed> */
    public function getWorkflowRun(string $owner, string $repo, int $runId): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}")->json();
    }

    /**
     * @return Generator<int, array<string, mixed>>
     */
    public function listFailedWorkflowRuns(string $owner, string $repo): Generator
    {
        return $this->listWorkflowRuns($owner, $repo, 'failure');
    }

    /** @return array<string, mixed> */
    public function listWorkflowJobs(string $owner, string $repo, int $runId): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}/jobs")->json();
    }

    public function deleteWorkflowRun(string $owner, string $repo, int $runId): bool
    {
        return $this->request(RequestMethod::DELETE, "https://api.github.com/repos/{$owner}/{$repo}/actions/runs/{$runId}")->successful();
    }

    public function branchExists(string $owner, string $repo, string $branch): bool
    {
        return false;
    }

    public function createBranch(string $owner, string $repo, string $branch): void {}

    /** @param array<string, mixed> $data */
    private function request(RequestMethod $method, string $url, array $data = []): Response
    {
        return $this->transport->request($method, $url, $data, [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Fable5-Automation-Framework',
        ]);
    }
}
