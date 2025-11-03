<?php

namespace App\Services\Cloudflare;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    private string $apiToken;

    private string $accountId;

    private string $apiEndpoint;

    private int $timeout;

    private int $retryAttempts;

    private int $retryDelay;

    public function __construct()
    {
        $this->apiToken = config('cloudflare.api_token');
        $this->accountId = config('cloudflare.account_id');
        $this->apiEndpoint = config('cloudflare.api_endpoint');
        $this->timeout = config('cloudflare.timeout', 30);
        $this->retryAttempts = config('cloudflare.retry_attempts', 3);
        $this->retryDelay = config('cloudflare.retry_delay', 1000);
    }

    /**
     * Make a GET request to Cloudflare API
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, [], $params);
    }

    /**
     * Make a POST request to Cloudflare API
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Make a PUT request to Cloudflare API
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, $data);
    }

    /**
     * Make a DELETE request to Cloudflare API
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request to Cloudflare API with retry logic
     */
    private function request(string $method, string $endpoint, array $data = [], array $params = []): array
    {
        $url = "{$this->apiEndpoint}/{$endpoint}";

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ])
                    ->timeout($this->timeout)
                    ->when($method === 'GET', fn ($request) => $request->get($url, $params))
                    ->when($method === 'POST', fn ($request) => $request->post($url, $data))
                    ->when($method === 'PUT', fn ($request) => $request->put($url, $data))
                    ->when($method === 'DELETE', fn ($request) => $request->delete($url));

                if ($response->successful()) {
                    $body = $response->json();

                    return $body['result'] ?? $body;
                }

                // Handle rate limiting
                if ($response->status() === 429) {
                    $retryAfter = (int) $response->header('Retry-After', $this->retryDelay / 1000);
                    sleep($retryAfter);
                    $attempt++;

                    continue;
                }

                $this->handleError($response);
            } catch (\Exception $e) {
                $lastException = $e;
                Log::error("Cloudflare API request failed (attempt {$attempt})", [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);

                $attempt++;
                if ($attempt < $this->retryAttempts) {
                    usleep($this->retryDelay * 1000);
                }
            }
        }

        throw new \RuntimeException(
            "Cloudflare API request failed after {$this->retryAttempts} attempts: ".
            ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Handle API error responses
     */
    private function handleError($response): void
    {
        $body = $response->json();
        $errors = $body['errors'] ?? [];
        $errorMessage = $errors[0]['message'] ?? 'Unknown Cloudflare API error';

        Log::error('Cloudflare API error', [
            'status' => $response->status(),
            'errors' => $errors,
        ]);

        throw new \RuntimeException($errorMessage, $response->status());
    }
}
