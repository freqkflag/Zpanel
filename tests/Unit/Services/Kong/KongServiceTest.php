<?php

use App\Services\APIGateway\KongService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'api-gateway.kong_admin_url' => 'http://kong:8001',
        'api-gateway.kong_proxy_url' => 'http://kong:8000',
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
