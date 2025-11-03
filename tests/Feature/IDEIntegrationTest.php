<?php

use App\Models\User;
use App\Services\IDEService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('ide index page loads successfully', function () {
    $response = $this->get(route('ide.index'));

    $response->assertSuccessful();
    $response->assertSee('Integrated Development Environment');
});

test('ide service generates valid token', function () {
    $ideService = app(IDEService::class);

    $token = $ideService->generateToken($this->user->id);

    expect($token)->toBeString();
    expect(strlen($token))->toBe(32);

    // Verify token is cached
    $cachedData = Cache::get("ide_token_{$token}");
    expect($cachedData)->toBeArray();
    expect($cachedData['user_id'])->toBe($this->user->id);
});

test('ide service validates token correctly', function () {
    $ideService = app(IDEService::class);

    $token = $ideService->generateToken($this->user->id);
    $validatedData = $ideService->validateToken($token);

    expect($validatedData)->toBeArray();
    expect($validatedData['user_id'])->toBe($this->user->id);
});

test('ide service returns null for invalid token', function () {
    $ideService = app(IDEService::class);

    $validatedData = $ideService->validateToken('invalid-token-12345');

    expect($validatedData)->toBeNull();
});

test('ide service generates correct workspace path', function () {
    $ideService = app(IDEService::class);

    $path = $ideService->getWorkspacePath($this->user->id);

    expect($path)->toContain("user_{$this->user->id}");
});

test('ide service generates project-specific workspace path', function () {
    $ideService = app(IDEService::class);

    $path = $ideService->getWorkspacePath($this->user->id, 'project-123');

    expect($path)->toContain("user_{$this->user->id}");
    expect($path)->toContain('project_project-123');
});

test('ide url includes token and workspace', function () {
    $ideService = app(IDEService::class);

    $url = $ideService->getIDEUrl('test-token', '/workspace/user_1');

    expect($url)->toContain('test-token');
    expect($url)->toContain('/workspace/user_1');
});

test('ide token expires after configured time', function () {
    config(['ide.token_expiry' => 24]);

    $ideService = app(IDEService::class);
    $token = $ideService->generateToken($this->user->id);

    $cachedData = Cache::get("ide_token_{$token}");

    expect($cachedData['expires_at']->diffInHours(now()))->toBeGreaterThan(23);
    expect($cachedData['expires_at']->diffInHours(now()))->toBeLessThanOrEqual(24);
});
