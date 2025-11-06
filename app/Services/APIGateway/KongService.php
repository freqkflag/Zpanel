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

    /**
     * Get metrics from Prometheus endpoint
     */
    public function getMetrics(?string $serviceId = null): array
    {
        $prometheusUrl = config('api-gateway.prometheus_url', 'http://prometheus:9090');

        try {
            // Query Prometheus for Kong metrics
            $queries = [
                'requests_total' => 'sum(rate(kong_http_requests_total'.($serviceId ? '{service="'.$serviceId.'"}' : '').'[5m]))',
                'request_latency_p50' => 'histogram_quantile(0.50, sum(rate(kong_http_request_latency_ms_bucket'.($serviceId ? '{service="'.$serviceId.'"}' : '').'[5m])) by (le))',
                'request_latency_p95' => 'histogram_quantile(0.95, sum(rate(kong_http_request_latency_ms_bucket'.($serviceId ? '{service="'.$serviceId.'"}' : '').'[5m])) by (le))',
                'request_latency_p99' => 'histogram_quantile(0.99, sum(rate(kong_http_request_latency_ms_bucket'.($serviceId ? '{service="'.$serviceId.'"}' : '').'[5m])) by (le))',
                'status_2xx' => 'sum(rate(kong_http_requests_total'.($serviceId ? '{service="'.$serviceId.'"}' : '').'{code=~"2.."}[5m]))',
                'status_4xx' => 'sum(rate(kong_http_requests_total'.($serviceId ? '{service="'.$serviceId.'"}' : '').'{code=~"4.."}[5m]))',
                'status_5xx' => 'sum(rate(kong_http_requests_total'.($serviceId ? '{service="'.$serviceId.'"}' : '').'{code=~"5.."}[5m]))',
            ];

            $metrics = [];

            foreach ($queries as $key => $query) {
                try {
                    $response = Http::timeout(10)->get("{$prometheusUrl}/api/v1/query", [
                        'query' => $query,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['data']['result'][0]['value'][1])) {
                            $metrics[$key] = (float) $data['data']['result'][0]['value'][1];
                        } else {
                            $metrics[$key] = 0;
                        }
                    } else {
                        $metrics[$key] = 0;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch Prometheus metric', [
                        'metric' => $key,
                        'error' => $e->getMessage(),
                    ]);
                    $metrics[$key] = 0;
                }
            }

            // Calculate error rate
            $totalRequests = $metrics['status_2xx'] + $metrics['status_4xx'] + $metrics['status_5xx'];
            $metrics['error_rate'] = $totalRequests > 0
                ? (($metrics['status_4xx'] + $metrics['status_5xx']) / $totalRequests) * 100
                : 0;

            return $metrics;
        } catch (\Exception $e) {
            Log::error('Failed to fetch Kong metrics from Prometheus', [
                'prometheus_url' => $prometheusUrl,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get service-specific metrics
     */
    public function getServiceMetrics(string $serviceId, ?string $timeRange = '24h'): array
    {
        $metrics = $this->getMetrics($serviceId);

        // Get time range data
        $prometheusUrl = config('api-gateway.prometheus_url', 'http://prometheus:9090');
        $range = match ($timeRange) {
            '1h' => '1h',
            '24h' => '24h',
            '7d' => '7d',
            default => '24h',
        };

        try {
            // Get request count for time range
            $query = 'sum(increase(kong_http_requests_total{service="'.$serviceId.'"}['.$range.']))';
            $response = Http::timeout(10)->get("{$prometheusUrl}/api/v1/query", [
                'query' => $query,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']['result'][0]['value'][1])) {
                    $metrics['requests_'.$timeRange] = (int) $data['data']['result'][0]['value'][1];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch time range metrics', [
                'service_id' => $serviceId,
                'time_range' => $timeRange,
                'error' => $e->getMessage(),
            ]);
        }

        return $metrics;
    }

    /**
     * Get metrics from Kong's built-in metrics endpoint (if Prometheus plugin is enabled)
     */
    public function getKongMetrics(?string $serviceId = null): array
    {
        try {
            $endpoint = $serviceId
                ? "{$this->adminUrl}/services/{$serviceId}/metrics"
                : "{$this->adminUrl}/metrics";

            $response = Http::timeout(10)->get($endpoint);

            if ($response->successful()) {
                // Parse Prometheus format metrics
                $metricsText = $response->body();

                return $this->parsePrometheusMetrics($metricsText);
            }

            return [];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Kong metrics endpoint', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
            ]);

            // Fallback to Prometheus if Kong metrics endpoint not available
            return $this->getMetrics($serviceId);
        }
    }

    /**
     * Parse Prometheus format metrics text
     */
    private function parsePrometheusMetrics(string $metricsText): array
    {
        $metrics = [];
        $lines = explode("\n", $metricsText);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Parse metric line: metric_name{labels} value
            if (preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*)\s+([0-9.]+)/', $line, $matches)) {
                $metricName = $matches[1];
                $value = (float) $matches[2];

                // Extract key metrics
                if (str_contains($metricName, 'kong_http_requests_total')) {
                    $metrics['requests_total'] = ($metrics['requests_total'] ?? 0) + $value;
                } elseif (str_contains($metricName, 'kong_http_request_latency')) {
                    $metrics['request_latency'] = $value;
                }
            }
        }

        return $metrics;
    }
}
