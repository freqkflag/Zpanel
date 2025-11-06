<?php

use App\Services\GitHub\GitHubService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.github.token' => 'test-github-token',
    ]);

    $this->service = new GitHubService;
});

test('github service lists repositories', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            ['id' => 1, 'name' => 'repo1', 'full_name' => 'user/repo1'],
            ['id' => 2, 'name' => 'repo2', 'full_name' => 'user/repo2'],
        ], 200),
    ]);

    $result = $this->service->listRepositories('user');

    expect($result)->toBeArray();
    expect($result)->toHaveCount(2);
});

test('github service gets repository details', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            'id' => 123,
            'name' => 'test-repo',
            'full_name' => 'user/test-repo',
            'description' => 'Test repository',
        ], 200),
    ]);

    $result = $this->service->getRepository('user', 'test-repo');

    expect($result)->toBeArray();
    expect($result['name'])->toBe('test-repo');
});

test('github service creates repository', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            'id' => 456,
            'name' => 'new-repo',
            'full_name' => 'user/new-repo',
        ], 201),
    ]);

    $result = $this->service->createRepository('new-repo', [
        'description' => 'New repository',
        'private' => false,
    ]);

    expect($result)->toBeArray();
    expect($result['name'])->toBe('new-repo');
});

test('github service handles api errors', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            'message' => 'Not Found',
        ], 404),
    ]);

    expect(fn () => $this->service->getRepository('user', 'nonexistent'))
        ->toThrow(RuntimeException::class);
});

test('github service includes authorization header', function () {
    Http::fake();

    try {
        $this->service->listRepositories('user');
    } catch (\Exception $e) {
        // Expected to fail with fake
    }

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'token test-github-token');
    });
});
