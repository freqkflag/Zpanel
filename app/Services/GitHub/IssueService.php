<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GitHub Issue Service
 *
 * Manages GitHub issues including creation, updating,
 * labeling, and commenting.
 */
class IssueService
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
            Log::error('GitHub Issue API error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * List issues for a repository
     */
    public function list(string $owner, string $repo, array $params = []): array
    {
        $defaultParams = [
            'state' => 'open',
            'sort' => 'created',
            'direction' => 'desc',
        ];

        return $this->request('GET', "/repos/{$owner}/{$repo}/issues", array_merge($defaultParams, $params));
    }

    /**
     * Get a single issue
     */
    public function get(string $owner, string $repo, int $issueNumber): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/issues/{$issueNumber}");
    }

    /**
     * Create an issue
     */
    public function create(string $owner, string $repo, array $data): array
    {
        if (! isset($data['title'])) {
            throw new \InvalidArgumentException('Issue title is required');
        }

        return $this->request('POST', "/repos/{$owner}/{$repo}/issues", $data);
    }

    /**
     * Update an issue
     */
    public function update(string $owner, string $repo, int $issueNumber, array $data): array
    {
        return $this->request('PATCH', "/repos/{$owner}/{$repo}/issues/{$issueNumber}", $data);
    }

    /**
     * Close an issue
     */
    public function close(string $owner, string $repo, int $issueNumber): array
    {
        return $this->update($owner, $repo, $issueNumber, ['state' => 'closed']);
    }

    /**
     * Reopen an issue
     */
    public function reopen(string $owner, string $repo, int $issueNumber): array
    {
        return $this->update($owner, $repo, $issueNumber, ['state' => 'open']);
    }

    /**
     * Add labels to an issue
     */
    public function addLabels(string $owner, string $repo, int $issueNumber, array $labels): array
    {
        return $this->request('POST', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/labels", [
            'labels' => $labels,
        ]);
    }

    /**
     * Remove labels from an issue
     */
    public function removeLabel(string $owner, string $repo, int $issueNumber, string $label): bool
    {
        try {
            $this->request('DELETE', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/labels/{$label}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * List comments on an issue
     */
    public function listComments(string $owner, string $repo, int $issueNumber): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments");
    }

    /**
     * Create a comment on an issue
     */
    public function createComment(string $owner, string $repo, int $issueNumber, string $body): array
    {
        return $this->request('POST', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/comments", [
            'body' => $body,
        ]);
    }

    /**
     * Update a comment
     */
    public function updateComment(string $owner, string $repo, int $commentId, string $body): array
    {
        return $this->request('PATCH', "/repos/{$owner}/{$repo}/issues/comments/{$commentId}", [
            'body' => $body,
        ]);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(string $owner, string $repo, int $commentId): bool
    {
        try {
            $this->request('DELETE', "/repos/{$owner}/{$repo}/issues/comments/{$commentId}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Assign users to an issue
     */
    public function assignUsers(string $owner, string $repo, int $issueNumber, array $assignees): array
    {
        return $this->request('POST', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/assignees", [
            'assignees' => $assignees,
        ]);
    }

    /**
     * Lock an issue
     */
    public function lock(string $owner, string $repo, int $issueNumber, ?string $reason = null): bool
    {
        $data = [];
        if ($reason) {
            $data['lock_reason'] = $reason; // 'off-topic', 'too heated', 'resolved', 'spam'
        }

        try {
            $this->request('PUT', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/lock", $data);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Unlock an issue
     */
    public function unlock(string $owner, string $repo, int $issueNumber): bool
    {
        try {
            $this->request('DELETE', "/repos/{$owner}/{$repo}/issues/{$issueNumber}/lock");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
