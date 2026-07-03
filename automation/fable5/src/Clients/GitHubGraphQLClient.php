<?php

declare(strict_types=1);

namespace Fable5\Clients;

use Fable5\Http\ApiClient;
use Fable5\Http\RequestMethod;

final class GitHubGraphQLClient
{
    private const ENDPOINT = 'https://api.github.com/graphql';

    public function __construct(
        private readonly ApiClient $transport,
        private readonly string $token,
    ) {}

    public function query(string $query, array $variables = []): array
    {
        return $this->transport->request(RequestMethod::POST, self::ENDPOINT, [
            'query'     => $query,
            'variables' => $variables,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/vnd.github.v3+json',
            'User-Agent'    => 'Fable5-Automation-Framework',
        ])->json();
    }

    public function getIssue(string $owner, string $repo, int $number): array
    {
        $query = <<<'GRAPHQL'
        query($owner: String!, $repo: String!, $number: Int!) {
          repository(owner: $owner, name: $repo) {
            issue(number: $number) {
              id
              title
              body
              state
              author { login }
              labels(first: 10) {
                nodes { name }
              }
              comments(first: 10) {
                nodes { body author { login } }
              }
            }
          }
        }
        GRAPHQL;

        return $this->query($query, ['owner' => $owner, 'repo' => $repo, 'number' => $number]);
    }

    public function getProject(string $owner, int $number): array
    {
        $query = <<<'GRAPHQL'
        query($owner: String!, $number: Int!) {
          user(login: $owner) {
            projectV2(number: $number) {
              id
              title
              url
              items(first: 20) {
                nodes {
                  id
                  content {
                    ... on Issue { title number }
                    ... on PullRequest { title number }
                  }
                }
              }
            }
          }
          organization(login: $owner) {
            projectV2(number: $number) {
              id
              title
              url
              items(first: 20) {
                nodes {
                  id
                  content {
                    ... on Issue { title number }
                    ... on PullRequest { title number }
                  }
                }
              }
            }
          }
        }
        GRAPHQL;

        return $this->query($query, ['owner' => $owner, 'number' => $number]);
    }

    public function listWorkflowRuns(string $owner, string $repo, int $first = 10): array
    {
        $query = <<<'GRAPHQL'
        query($owner: String!, $repo: String!, $first: Int!) {
          repository(owner: $owner, name: $repo) {
            databaseId
            object(expression: "HEAD") {
              ... on Commit {
                checkSuites(first: $first) {
                  nodes {
                    workflowRun {
                      databaseId
                      url
                      status
                      conclusion
                    }
                  }
                }
              }
            }
          }
        }
        GRAPHQL;

        return $this->query($query, ['owner' => $owner, 'repo' => $repo, 'first' => $first]);
    }

    public function addProjectV2ItemById(string $projectId, string $contentId): array
    {
        $query = <<<'GRAPHQL'
        mutation($projectId: ID!, $contentId: ID!) {
          addProjectV2ItemById(input: {projectId: $projectId, contentId: $contentId}) {
            item { id }
          }
        }
        GRAPHQL;

        return $this->query($query, ['projectId' => $projectId, 'contentId' => $contentId]);
    }
}
