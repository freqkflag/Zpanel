<?php

use App\Services\Database\QueryExecutor;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->executor = new QueryExecutor;
});

test('query executor executes read queries', function () {
    DB::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    DB::shouldReceive('select')
        ->once()
        ->with('SELECT * FROM users WHERE id = ?', [1])
        ->andReturn([['id' => 1, 'name' => 'Test']]);

    $result = $this->executor->executeRead('mysql', 'SELECT * FROM users WHERE id = ?', [1]);

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['rows'][0]['id'])->toBe(1);
});

test('query executor prevents dangerous operations', function () {
    $result1 = $this->executor->executeWrite('mysql', 'DROP DATABASE test');
    expect($result1)->toBeArray();
    expect($result1['success'])->toBeFalse();
    expect($result1['error'])->toContain('Dangerous query operations are not allowed');

    $result2 = $this->executor->executeWrite('mysql', 'TRUNCATE TABLE users');
    expect($result2)->toBeArray();
    expect($result2['success'])->toBeFalse();
    expect($result2['error'])->toContain('Dangerous query operations are not allowed');
});

test('query executor validates query syntax', function () {
    DB::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    DB::shouldReceive('select')
        ->once()
        ->andThrow(new \Exception('SQL syntax error'));

    $result = $this->executor->executeRead('mysql', 'INVALID SQL SYNTAX');

    expect($result)->toBeArray();
    expect($result['success'])->toBeFalse();
});

test('query executor explains queries', function () {
    DB::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    DB::shouldReceive('getDriverName')
        ->once()
        ->andReturn('mysql');
    DB::shouldReceive('select')
        ->once()
        ->with('EXPLAIN FORMAT=JSON SELECT * FROM users', [])
        ->andReturn([['explain' => 'Seq Scan on users']]);

    $result = $this->executor->explainQuery('mysql', 'SELECT * FROM users');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
});
