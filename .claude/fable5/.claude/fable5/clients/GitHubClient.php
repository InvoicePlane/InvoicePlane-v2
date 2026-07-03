<?php

declare(strict_types=1);

final class GitHubClient
{
    public function __construct(
        private string $owner,
        private string $repo,
        private string $token,
    ) {}

    public function fetchOpenPullRequests(): array
    {
        return $this->request('/pulls?state=open');
    }

    public function fetchPullRequest(int $number): array
    {
        return $this->request('/pulls/' . $number);
    }

    private function request(string $endpoint): array
    {
        $url = sprintf(
            'https://api.github.com/repos/%s/%s%s',
            $this->owner,
            $this->repo,
            $endpoint
        );

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github+json',
                'Authorization: Bearer ' . $this->token,
                'User-Agent: Fable5-Agent',
            ],
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);
            return [];
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        return is_array($decoded) ? $decoded : [];
    }
}
