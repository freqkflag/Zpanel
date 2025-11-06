<?php

namespace App\Livewire\ApiGateway;

use App\Models\APIService;
use App\Services\APIGateway\KongService;
use Livewire\Component;

class ServiceAnalytics extends Component
{
    public APIService $service;

    public array $analytics = [];

    public bool $loading = true;

    public function mount(APIService $service)
    {
        $this->service = $service;
    }

    public function loadAnalytics()
    {
        try {
            $kongService = app(KongService::class);

            // Get service metrics from Prometheus or Kong metrics endpoint
            $metrics = $kongService->getServiceMetrics(
                $this->service->kong_service_id,
                '24h'
            );

            // Format metrics for display
            $this->analytics = [
                'requests' => [
                    'total' => (int) ($metrics['requests_total'] ?? 0),
                    'last_24h' => (int) ($metrics['requests_24h'] ?? 0),
                    'last_7d' => (int) ($metrics['requests_7d'] ?? 0),
                ],
                'response_time' => [
                    'p50' => round($metrics['request_latency_p50'] ?? 0, 2),
                    'p95' => round($metrics['request_latency_p95'] ?? 0, 2),
                    'p99' => round($metrics['request_latency_p99'] ?? 0, 2),
                ],
                'error_rate' => round($metrics['error_rate'] ?? 0, 2),
                'status_codes' => [
                    '2xx' => (int) ($metrics['status_2xx'] ?? 0),
                    '4xx' => (int) ($metrics['status_4xx'] ?? 0),
                    '5xx' => (int) ($metrics['status_5xx'] ?? 0),
                ],
            ];

            $this->loading = false;
        } catch (\Exception $e) {
            $this->loading = false;
            session()->flash('error', 'Failed to load analytics: '.$e->getMessage());
        }
    }

    public function render()
    {
        if ($this->loading) {
            $this->loadAnalytics();
        }

        return view('livewire.api-gateway.service-analytics');
    }
}
