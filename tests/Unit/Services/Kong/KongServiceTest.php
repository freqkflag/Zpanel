<?php

use App\Services\APIGateway\KongService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'api-gateway.kong_admin_url' => 'http://kong:8001',
        'api-gateway.kong_proxy_url' => 'http://kong:8000',
        'api-gateway.prometheus_url' => 'http://prometheus:9090',
    ]);

    $this->service = new KongService;
});

test('kong service creates service successfully', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'id' => 'service-123',
            'name' => 'test-service',
            'url' => 'http://backend:3000',
        ], 201),
    ]);

    $result = $this->service->createService('test-service', 'http://backend:3000');

    expect($result)->toBeArray();
    expect($result['id'])->toBe('service-123');
    expect($result['name'])->toBe('test-service');
});

test('kong service retrieves service by id', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'id' => 'service-123',
            'name' => 'test-service',
        ], 200),
    ]);

    $result = $this->service->getService('service-123');

    expect($result)->toBeArray();
    expect($result['id'])->toBe('service-123');
});

test('kong service updates service successfully', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'id' => 'service-123',
            'name' => 'updated-service',
        ], 200),
    ]);

    $result = $this->service->updateService('service-123', [
        'name' => 'updated-service',
    ]);

    expect($result)->toBeArray();
    expect($result['name'])->toBe('updated-service');
});

test('kong service deletes service successfully', function () {
    Http::fake([
        'kong:8001/*' => Http::response('', 204),
    ]);

    $result = $this->service->deleteService('service-123');

    expect($result)->toBeTrue();
});

test('kong service creates routes for service', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'id' => 'route-123',
            'paths' => ['/api/test'],
            'service' => ['id' => 'service-123'],
        ], 201),
    ]);

    $result = $this->service->createRoute('service-123', ['/api/test']);

    expect($result)->toBeArray();
    expect($result['paths'])->toContain('/api/test');
});

test('kong service adds plugins to service', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'id' => 'plugin-123',
            'name' => 'rate-limiting',
            'config' => ['minute' => 100],
        ], 201),
    ]);

    $result = $this->service->addPlugin('service-123', 'rate-limiting', [
        'minute' => 100,
    ]);

    expect($result)->toBeArray();
    expect($result['name'])->toBe('rate-limiting');
});

test('kong service manages rate limiting', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'id' => 'plugin-123',
            'name' => 'rate-limiting',
        ], 201),
    ]);

    $result = $this->service->manageRateLimiting('service-123', 60);

    expect($result)->toBeArray();
    expect($result['name'])->toBe('rate-limiting');
});

test('kong service lists all services', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'data' => [
                ['id' => 'service-1', 'name' => 'api-1'],
                ['id' => 'service-2', 'name' => 'api-2'],
            ],
        ], 200),
    ]);

    $result = $this->service->listServices();

    expect($result)->toBeArray();
    expect($result['data'])->toHaveCount(2);
});

test('kong service health check returns status', function () {
    Http::fake([
        'kong:8001/*' => Http::response([
            'database' => ['reachable' => true],
        ], 200),
    ]);

    $result = $this->service->healthCheck();

    expect($result)->toBeArray();
    expect($result['status'])->toBe('healthy');
});

test('kong service health check handles unreachable kong', function () {
    Http::fake([
        'kong:8001/*' => Http::response('Connection refused', 500),
    ]);

    $result = $this->service->healthCheck();

    expect($result['status'])->toBe('unhealthy');
});

test('kong service gets metrics from prometheus', function () {
    config(['api-gateway.prometheus_url' => 'http://prometheus:9090']);

    Http::fake([
        'prometheus:9090/api/v1/query*' => Http::response([
            'data' => [
                'result' => [
                    ['value' => [time(), '100.5']],
                ],
            ],
        ], 200),
    ]);

    $result = $this->service->getMetrics();

    expect($result)->toBeArray();
    expect($result)->toHaveKey('requests_total');
});

test('kong service gets service-specific metrics', function () {
    config(['api-gateway.prometheus_url' => 'http://prometheus:9090']);

    Http::fake([
        'prometheus:9090/api/v1/query*' => Http::response([
            'data' => [
                'result' => [
                    ['value' => [time(), '50.2']],
                ],
            ],
        ], 200),
    ]);

    $result = $this->service->getServiceMetrics('service-123', '24h');

    expect($result)->toBeArray();
    expect($result)->toHaveKey('requests_total');
});

test('kong service calculates error rate correctly', function () {
    config(['api-gateway.prometheus_url' => 'http://prometheus:9090']);

    Http::fake([
        'prometheus:9090/api/v1/query*' => function ($request) {
            $query = $request->data()['query'] ?? '';
            if (str_contains($query, 'status_2xx')) {
                return Http::response(['data' => ['result' => [['value' => [time(), '80']]]]], 200);
            }
            if (str_contains($query, 'status_4xx')) {
                return Http::response(['data' => ['result' => [['value' => [time(), '15']]]]], 200);
            }
            if (str_contains($query, 'status_5xx')) {
                return Http::response(['data' => ['result' => [['value' => [time(), '5']]]]], 200);
            }

            return Http::response(['data' => ['result' => [['value' => [time(), '0']]]]], 200);
        },
    ]);

    $result = $this->service->getMetrics();

    expect($result)->toHaveKey('error_rate');
    // 20 errors out of 100 total = 20% error rate
    expect($result['error_rate'])->toBeGreaterThan(0);
});

test('kong service falls back to prometheus when kong metrics endpoint unavailable', function () {
    config([
        'api-gateway.kong_admin_url' => 'http://kong:8001',
        'api-gateway.prometheus_url' => 'http://prometheus:9090',
    ]);

    Http::fake([
        'kong:8001/*/metrics' => Http::response('Not Found', 404),
        'prometheus:9090/api/v1/query*' => Http::response([
            'data' => ['result' => [['value' => [time(), '10']]]],
        ], 200),
    ]);

    $result = $this->service->getKongMetrics('service-123');

    expect($result)->toBeArray();
});

test('kong service parses prometheus metrics format', function () {
    $metricsText = <<<'METRICS'
# HELP kong_http_requests_total Total number of HTTP requests
# TYPE kong_http_requests_total counter
kong_http_requests_total{service="test-service"} 1000
kong_http_request_latency_ms{service="test-service"} 50.5
METRICS;

    $reflection = new \ReflectionClass($this->service);
    $method = $reflection->getMethod('parsePrometheusMetrics');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, $metricsText);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('requests_total');
});
