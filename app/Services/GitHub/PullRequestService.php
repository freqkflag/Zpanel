<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GitHub Pull Request Service
 *
 * Manages GitHub pull requests including creation,
 * merging, reviewing, and automation.
 */
class PullRequestService
{
    private string $apiUrl = 'https://api.github.com';

    private ?string $token = null;

    public function __construct()
    {
        $this->token = config('services.github.token');
    }

    /**
     * Make authenticated request to GitHub API
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = str_starts_with($endpoint, 'http') ? $endpoint : $this->apiUrl.$endpoint;

        $request = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'Authorization' => 'Bearer '.$this->token,
            'X-GitHub-Api-Version' => '2022-11-28',
        ]);

        try {
            $response = match ($method) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                'PATCH' => $request->patch($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new \InvalidArgumentException('Invalid HTTP method'),
            };

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            throw new \RuntimeException('GitHub API request failed: '.$response->body());
        } catch (\Exception $e) {
            Log::error('GitHub PR API error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * List pull requests for a repository
     */
    public function list(string $owner, string $repo, array $params = []): array
    {
        $defaultParams = [
            'state' => 'open',
            'sort' => 'created',
            'direction' => 'desc',
        ];

        return $this->request('GET', "/repos/{$owner}/{$repo}/pulls", array_merge($defaultParams, $params));
    }

    /**
     * Get a single pull request
     */
    public function get(string $owner, string $repo, int $pullNumber): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}");
    }

    /**
     * Create a pull request
     */
    public function create(string $owner, string $repo, array $data): array
    {
        $required = ['title', 'head', 'base'];
        foreach ($required as $field) {
            if (! isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        return $this->request('POST', "/repos/{$owner}/{$repo}/pulls", $data);
    }

    /**
     * Update a pull request
     */
    public function update(string $owner, string $repo, int $pullNumber, array $data): array
    {
        return $this->request('PATCH', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}", $data);
    }

    /**
     * Merge a pull request
     */
    public function merge(string $owner, string $repo, int $pullNumber, array $data = []): array
    {
        $defaultData = [
            'merge_method' => 'merge', // 'merge', 'squash', or 'rebase'
        ];

        return $this->request('PUT', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/merge", array_merge($defaultData, $data));
    }

    /**
     * Close a pull request
     */
    public function close(string $owner, string $repo, int $pullNumber): array
    {
        return $this->update($owner, $repo, $pullNumber, ['state' => 'closed']);
    }

    /**
     * Reopen a pull request
     */
    public function reopen(string $owner, string $repo, int $pullNumber): array
    {
        return $this->update($owner, $repo, $pullNumber, ['state' => 'open']);
    }

    /**
     * List pull request reviews
     */
    public function listReviews(string $owner, string $repo, int $pullNumber): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/reviews");
    }

    /**
     * Create a pull request review
     */
    public function createReview(string $owner, string $repo, int $pullNumber, array $data): array
    {
        return $this->request('POST', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/reviews", $data);
    }

    /**
     * Submit a pull request review
     */
    public function submitReview(string $owner, string $repo, int $pullNumber, int $reviewId, string $event, ?string $body = null): array
    {
        $data = ['event' => $event]; // 'APPROVE', 'REQUEST_CHANGES', 'COMMENT'
        if ($body) {
            $data['body'] = $body;
        }

        return $this->request('POST', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/reviews/{$reviewId}/events", $data);
    }

    /**
     * List pull request commits
     */
    public function listCommits(string $owner, string $repo, int $pullNumber): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/commits");
    }

    /**
     * List pull request files
     */
    public function listFiles(string $owner, string $repo, int $pullNumber): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/files");
    }

    /**
     * Check if pull request is merged
     */
    public function isMerged(string $owner, string $repo, int $pullNumber): bool
    {
        try {
            $this->request('GET', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/merge");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Request reviewers for a pull request
     */
    public function requestReviewers(string $owner, string $repo, int $pullNumber, array $reviewers = [], array $teamReviewers = []): array
    {
        $data = [];
        if (! empty($reviewers)) {
            $data['reviewers'] = $reviewers;
        }
        if (! empty($teamReviewers)) {
            $data['team_reviewers'] = $teamReviewers;
        }

        return $this->request('POST', "/repos/{$owner}/{$repo}/pulls/{$pullNumber}/requested_reviewers", $data);
    }

    /**
     * Add labels to a pull request
     */
    public function addLabels(string $owner, string $repo, int $pullNumber, array $labels): array
    {
        return $this->request('POST', "/repos/{$owner}/{$repo}/issues/{$pullNumber}/labels", [
            'labels' => $labels,
        ]);
    }
}
