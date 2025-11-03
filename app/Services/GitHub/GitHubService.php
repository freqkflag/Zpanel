<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GitHub API Service
 *
 * Complete GitHub API integration for repository management,
 * pull requests, issues, and GitHub Actions.
 */
class GitHubService
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
            Log::error('GitHub API error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get authenticated user info
     */
    public function getCurrentUser(): array
    {
        return $this->request('GET', '/user');
    }

    /**
     * List user repositories
     */
    public function listRepositories(?string $username = null, array $params = []): array
    {
        $endpoint = $username ? "/users/{$username}/repos" : '/user/repos';

        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Get repository details
     */
    public function getRepository(string $owner, string $repo): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}");
    }

    /**
     * Create a new repository
     */
    public function createRepository(array $data): array
    {
        return $this->request('POST', '/user/repos', $data);
    }

    /**
     * Fork a repository
     */
    public function forkRepository(string $owner, string $repo, array $data = []): array
    {
        return $this->request('POST', "/repos/{$owner}/{$repo}/forks", $data);
    }

    /**
     * Delete a repository
     */
    public function deleteRepository(string $owner, string $repo): bool
    {
        try {
            $this->request('DELETE', "/repos/{$owner}/{$repo}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get repository branches
     */
    public function getBranches(string $owner, string $repo): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/branches");
    }

    /**
     * Get repository tags
     */
    public function getTags(string $owner, string $repo): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/tags");
    }

    /**
     * Get repository commits
     */
    public function getCommits(string $owner, string $repo, array $params = []): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/commits", $params);
    }

    /**
     * Get file contents
     */
    public function getFileContents(string $owner, string $repo, string $path, string $ref = 'main'): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/contents/{$path}", ['ref' => $ref]);
    }

    /**
     * Create or update file contents
     */
    public function createOrUpdateFile(string $owner, string $repo, string $path, array $data): array
    {
        return $this->request('PUT', "/repos/{$owner}/{$repo}/contents/{$path}", $data);
    }

    /**
     * Delete file
     */
    public function deleteFile(string $owner, string $repo, string $path, string $message, string $sha): array
    {
        return $this->request('DELETE', "/repos/{$owner}/{$repo}/contents/{$path}", [
            'message' => $message,
            'sha' => $sha,
        ]);
    }

    /**
     * Search repositories
     */
    public function searchRepositories(string $query, array $params = []): array
    {
        $params['q'] = $query;

        return $this->request('GET', '/search/repositories', $params);
    }

    /**
     * Get repository collaborators
     */
    public function getCollaborators(string $owner, string $repo): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/collaborators");
    }

    /**
     * Add collaborator to repository
     */
    public function addCollaborator(string $owner, string $repo, string $username, string $permission = 'push'): array
    {
        return $this->request('PUT', "/repos/{$owner}/{$repo}/collaborators/{$username}", [
            'permission' => $permission,
        ]);
    }
}
