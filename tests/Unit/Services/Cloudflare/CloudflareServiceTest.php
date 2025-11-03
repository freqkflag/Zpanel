<?php

use App\Services\Cloudflare\CloudflareService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'cloudflare.api_token' => 'test-token',
        'cloudflare.account_id' => 'test-account',
        'cloudflare.api_endpoint' => 'https://api.cloudflare.com/client/v4',
    ]);

    $this->service = new CloudflareService;
});

test('cloudflare service makes get requests correctly', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response([
            'success' => true,
            'result' => ['zone_id' => '123'],
        ], 200),
    ]);

    $result = $this->service->get('zones');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
});

test('cloudflare service makes post requests correctly', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response([
            'success' => true,
            'result' => ['dns_record_id' => '456'],
        ], 200),
    ]);

    $result = $this->service->post('zones/123/dns_records', [
        'type' => 'A',
        'name' => 'test.example.com',
        'content' => '192.0.2.1',
    ]);

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
});

test('cloudflare service handles errors gracefully', function () {
    Http::fake([
        'api.cloudflare.com/*' => Http::response([
            'success' => false,
            'errors' => [['message' => 'Invalid token']],
        ], 403),
    ]);

    expect(fn () => $this->service->get('zones'))
        ->toThrow(RuntimeException::class);
});

test('cloudflare service includes authorization header', function () {
    Http::fake();

    try {
        $this->service->get('zones');
    } catch (\Exception $e) {
        // Expected to fail with fake
    }

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer test-token');
    });
});
