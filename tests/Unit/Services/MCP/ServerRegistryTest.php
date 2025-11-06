<?php

use App\Models\MCPServer;
use App\Services\MCP\ServerRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Skip tests if SQLite driver is not available
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('SQLite extension is required for these tests');
    }

    $this->registry = app(ServerRegistry::class);
});

test('can register new mcp server', function () {
    $server = $this->registry->register('test-server', [
        'type' => 'cloudflare',
        'command' => 'php',
        'args' => ['artisan', 'test'],
    ]);

    expect($server)->toBeInstanceOf(MCPServer::class);
    expect($server->name)->toBe('test-server');
    expect($server->type)->toBe('cloudflare');
    expect($server->status)->toBe('active');
});

test('can get server by name', function () {
    \App\Models\MCPServer::factory()->create(['name' => 'my-server', 'status' => 'active']);

    $server = $this->registry->getServer('my-server');

    expect($server)->not->toBeNull();
    expect($server->name)->toBe('my-server');
});

test('get server returns null for inactive server', function () {
    \App\Models\MCPServer::factory()->create(['name' => 'inactive-server', 'status' => 'inactive']);

    $server = $this->registry->getServer('inactive-server');

    expect($server)->toBeNull();
});

test('lists only active servers', function () {
    \App\Models\MCPServer::factory()->count(3)->create(['status' => 'active']);
    \App\Models\MCPServer::factory()->count(2)->create(['status' => 'inactive']);

    $servers = $this->registry->listServers();

    expect($servers)->toHaveCount(3);
});

test('can update server status', function () {
    $server = \App\Models\MCPServer::factory()->create(['name' => 'test-server', 'status' => 'active']);

    $result = $this->registry->updateStatus('test-server', 'inactive');

    expect($result)->toBeTrue();
    $server->refresh();
    expect($server->status)->toBe('inactive');
});

test('update status returns false for non-existent server', function () {
    $result = $this->registry->updateStatus('non-existent', 'active');

    expect($result)->toBeFalse();
});

test('health check updates last check timestamp', function () {
    $server = \App\Models\MCPServer::factory()->create(['name' => 'test-server']);

    $this->travel(1)->hour();

    $result = $this->registry->healthCheck('test-server');

    expect($result['status'])->toBe('healthy');
    $server->refresh();
    expect($server->last_health_check)->not->toBeNull();
});

test('health check returns not found for missing server', function () {
    $result = $this->registry->healthCheck('non-existent');

    expect($result['status'])->toBe('not_found');
});

test('generate config includes laravel boost', function () {
    \App\Models\MCPServer::factory()->create(['name' => 'custom-server', 'status' => 'active']);

    $config = $this->registry->generateConfig();

    expect($config)->toBeArray();
    expect($config['mcpServers'])->toHaveKey('laravel-boost');
    expect($config['mcpServers'])->toHaveKey('custom-server');
});

test('generate config excludes inactive servers', function () {
    \App\Models\MCPServer::factory()->create(['name' => 'active-server', 'status' => 'active']);
    \App\Models\MCPServer::factory()->create(['name' => 'inactive-server', 'status' => 'inactive']);

    $config = $this->registry->generateConfig();

    expect($config['mcpServers'])->toHaveKey('active-server');
    expect($config['mcpServers'])->not->toHaveKey('inactive-server');
});
