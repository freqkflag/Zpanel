<?php

use App\Services\IDEService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'ide.code_server_url' => 'http://code-server:8080',
        'ide.workspace_base' => '/workspace',
        'ide.token_expiry' => 24,
    ]);

    $this->service = new IDEService;
});

test('generates unique tokens for each call', function () {
    $token1 = $this->service->generateToken(1);
    $token2 = $this->service->generateToken(1);

    expect($token1)->not->toBe($token2);
});

test('token is stored in cache with correct data', function () {
    $userId = 123;
    $token = $this->service->generateToken($userId);

    $cachedData = Cache::get("ide_token_{$token}");

    expect($cachedData)->toBeArray();
    expect($cachedData['user_id'])->toBe($userId);
    expect($cachedData['created_at'])->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('workspace path includes user id', function () {
    $path = $this->service->getWorkspacePath(42);

    expect($path)->toBe('/workspace/user_42');
});

test('workspace path includes project id when provided', function () {
    $path = $this->service->getWorkspacePath(42, 'proj-123');

    expect($path)->toBe('/workspace/user_42/project_proj-123');
});

test('ide url contains token and workspace parameters', function () {
    $url = $this->service->getIDEUrl('abc123', '/workspace/user_1');

    expect($url)->toContain('abc123');
    expect($url)->toContain('/workspace/user_1');
    expect($url)->toStartWith('http://code-server:8080');
});
