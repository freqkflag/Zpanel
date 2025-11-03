<?php

use App\Models\MCPServer;
use App\Models\User;
use App\Services\MCP\ServerRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('mcp servers index page loads successfully', function () {
    $response = $this->get(route('mcp.index'));

    $response->assertSuccessful();
    $response->assertSee('MCP Servers');
    $response->assertSee('Zpanel');
});

test('mcp server create page loads successfully', function () {
    $response = $this->get(route('mcp.create'));

    $response->assertSuccessful();
    $response->assertSee('Create MCP Server');
    $response->assertSee('Server Name');
    $response->assertSee('Server Type');
});

test('can create mcp server with valid configuration', function () {
    $response = $this->post(route('mcp.store'), [
        'name' => 'test-cloudflare-server',
        'type' => 'cloudflare',
        'config' => json_encode([
            'command' => 'php',
            'args' => ['artisan', 'mcp:cloudflare'],
            'api_token' => 'test-token',
        ]),
    ]);

    $response->assertRedirect(route('mcp.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('mcp_servers', [
        'name' => 'test-cloudflare-server',
        'type' => 'cloudflare',
        'status' => 'active',
    ]);
});

test('mcp server name must be unique', function () {
    MCPServer::factory()->create(['name' => 'existing-server']);

    $response = $this->post(route('mcp.store'), [
        'name' => 'existing-server',
        'type' => 'cloudflare',
        'config' => json_encode(['command' => 'php']),
    ]);

    $response->assertSessionHasErrors('name');
});

test('mcp server config must be valid json', function () {
    $response = $this->post(route('mcp.store'), [
        'name' => 'test-server',
        'type' => 'cloudflare',
        'config' => 'invalid-json-{',
    ]);

    $response->assertSessionHasErrors('config');
});

test('can update mcp server', function () {
    $server = MCPServer::factory()->create([
        'name' => 'original-name',
        'type' => 'cloudflare',
        'status' => 'active',
    ]);

    $response = $this->put(route('mcp.update', $server), [
        'name' => 'updated-name',
        'type' => 'github',
        'config' => json_encode(['command' => 'node']),
        'status' => 'inactive',
    ]);

    $response->assertRedirect(route('mcp.index'));

    $server->refresh();
    expect($server->name)->toBe('updated-name');
    expect($server->type)->toBe('github');
    expect($server->status)->toBe('inactive');
});

test('can delete mcp server', function () {
    $server = MCPServer::factory()->create();

    $response = $this->delete(route('mcp.destroy', $server));

    $response->assertRedirect(route('mcp.index'));
    $this->assertDatabaseMissing('mcp_servers', ['id' => $server->id]);
});

test('health check endpoint returns json status', function () {
    $server = MCPServer::factory()->create(['name' => 'test-server']);

    $response = $this->get(route('mcp.health', $server));

    $response->assertSuccessful();
    $response->assertJsonStructure(['status', 'name', 'checked_at']);
});

test('config endpoint generates valid mcp json', function () {
    MCPServer::factory()->create([
        'name' => 'test-server',
        'status' => 'active',
        'config' => ['command' => 'php', 'args' => ['artisan', 'test']],
    ]);

    $response = $this->get(route('mcp.config'));

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'mcpServers' => [
            'laravel-boost',
            'test-server',
        ],
    ]);
});

test('server registry can register new server', function () {
    $registry = app(ServerRegistry::class);

    $server = $registry->register('test-server', [
        'type' => 'cloudflare',
        'command' => 'php',
        'args' => ['artisan', 'test'],
    ]);

    expect($server)->toBeInstanceOf(MCPServer::class);
    expect($server->name)->toBe('test-server');
    expect($server->status)->toBe('active');
});

test('server registry lists only active servers', function () {
    MCPServer::factory()->create(['status' => 'active']);
    MCPServer::factory()->create(['status' => 'inactive']);
    MCPServer::factory()->create(['status' => 'error']);

    $registry = app(ServerRegistry::class);
    $servers = $registry->listServers();

    expect($servers)->toHaveCount(1);
    expect($servers[0]['status'])->toBe('active');
});
