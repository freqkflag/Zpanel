<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GitHub Actions Service
 *
 * Manages GitHub Actions workflows including triggering,
 * monitoring, and retrieving workflow runs.
 */
class ActionsService
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
            Log::error('GitHub Actions API error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * List workflows for a repository
     */
    public function listWorkflows(string $owner, string $repo): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/workflows");
    }

    /**
     * Get a workflow
     */
    public function getWorkflow(string $owner, string $repo, int $workflowId): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/workflows/{$workflowId}");
    }

    /**
     * List workflow runs
     */
    public function listWorkflowRuns(string $owner, string $repo, int $workflowId, array $params = []): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/workflows/{$workflowId}/runs", $params);
    }

    /**
     * Get a workflow run
     */
    public function getWorkflowRun(string $owner, string $repo, int $runId): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/runs/{$runId}");
    }

    /**
     * Trigger a workflow dispatch event
     */
    public function triggerWorkflow(string $owner, string $repo, int $workflowId, string $ref, array $inputs = []): bool
    {
        try {
            $this->request('POST', "/repos/{$owner}/{$repo}/actions/workflows/{$workflowId}/dispatches", [
                'ref' => $ref,
                'inputs' => $inputs,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cancel a workflow run
     */
    public function cancelWorkflowRun(string $owner, string $repo, int $runId): bool
    {
        try {
            $this->request('POST', "/repos/{$owner}/{$repo}/actions/runs/{$runId}/cancel");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Rerun a workflow
     */
    public function rerunWorkflow(string $owner, string $repo, int $runId): bool
    {
        try {
            $this->request('POST', "/repos/{$owner}/{$repo}/actions/runs/{$runId}/rerun");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * List workflow run jobs
     */
    public function listJobs(string $owner, string $repo, int $runId): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/runs/{$runId}/jobs");
    }

    /**
     * Get a job
     */
    public function getJob(string $owner, string $repo, int $jobId): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/jobs/{$jobId}");
    }

    /**
     * Download workflow run logs
     */
    public function downloadLogs(string $owner, string $repo, int $runId): string
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'Authorization' => 'Bearer '.$this->token,
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->get("{$this->apiUrl}/repos/{$owner}/{$repo}/actions/runs/{$runId}/logs");

        if ($response->successful()) {
            return $response->body();
        }

        throw new \RuntimeException('Failed to download logs: '.$response->body());
    }

    /**
     * List repository secrets
     */
    public function listSecrets(string $owner, string $repo): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/secrets");
    }

    /**
     * Get a repository secret
     */
    public function getSecret(string $owner, string $repo, string $secretName): array
    {
        return $this->request('GET', "/repos/{$owner}/{$repo}/actions/secrets/{$secretName}");
    }

    /**
     * Create or update a repository secret
     */
    public function createOrUpdateSecret(string $owner, string $repo, string $secretName, string $encryptedValue, string $keyId): array
    {
        return $this->request('PUT', "/repos/{$owner}/{$repo}/actions/secrets/{$secretName}", [
            'encrypted_value' => $encryptedValue,
            'key_id' => $keyId,
        ]);
    }

    /**
     * Delete a repository secret
     */
    public function deleteSecret(string $owner, string $repo, string $secretName): bool
    {
        try {
            $this->request('DELETE', "/repos/{$owner}/{$repo}/actions/secrets/{$secretName}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
