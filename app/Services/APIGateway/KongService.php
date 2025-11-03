<?php

namespace App\Services\APIGateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KongService
{
    private string $adminUrl;

    private string $proxyUrl;

    public function __construct()
    {
        $this->adminUrl = config('api-gateway.kong_admin_url');
        $this->proxyUrl = config('api-gateway.kong_proxy_url');
    }

    /**
     * Create a new service in Kong
     */
    public function createService(string $name, string $url): array
    {
        try {
            $response = Http::post("{$this->adminUrl}/services", [
                'name' => $name,
                'url' => $url,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('Failed to create Kong service: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Kong service creation failed', [
                'name' => $name,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get a service from Kong
     */
    public function getService(string $serviceId): array
    {
        try {
            $response = Http::get("{$this->adminUrl}/services/{$serviceId}");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('Failed to get Kong service: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Kong service retrieval failed', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update a service in Kong
     */
    public function updateService(string $serviceId, array $data): array
    {
        try {
            $response = Http::patch("{$this->adminUrl}/services/{$serviceId}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('Failed to update Kong service: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Kong service update failed', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a service from Kong
     */
    public function deleteService(string $serviceId): bool
    {
        try {
            $response = Http::delete("{$this->adminUrl}/services/{$serviceId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Kong service deletion failed', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create a route for a service
     */
    public function createRoute(string $serviceId, array $paths): array
    {
        try {
            $response = Http::post("{$this->adminUrl}/services/{$serviceId}/routes", [
                'paths' => $paths,
                'strip_path' => true,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('Failed to create Kong route: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Kong route creation failed', [
                'service_id' => $serviceId,
                'paths' => $paths,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Add a plugin to a service
     */
    public function addPlugin(string $serviceId, string $plugin, array $config): array
    {
        try {
            $response = Http::post("{$this->adminUrl}/services/{$serviceId}/plugins", [
                'name' => $plugin,
                'config' => $config,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('Failed to add Kong plugin: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Kong plugin addition failed', [
                'service_id' => $serviceId,
                'plugin' => $plugin,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Manage rate limiting for a service
     */
    public function manageRateLimiting(string $serviceId, int $limit): array
    {
        return $this->addPlugin($serviceId, 'rate-limiting', [
            'minute' => $limit,
            'policy' => 'local',
        ]);
    }

    /**
     * List all services in Kong
     */
    public function listServices(): array
    {
        try {
            $response = Http::get("{$this->adminUrl}/services");

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('Failed to list Kong services: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Kong services listing failed', [
                'error' => $e->getMessage(),
            ]);

            return ['data' => []];
        }
    }

    /**
     * Check Kong health
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->adminUrl}/status");

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'data' => $response->json(),
                ];
            }

            return [
                'status' => 'unhealthy',
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unreachable',
                'error' => $e->getMessage(),
            ];
        }
    }
}
