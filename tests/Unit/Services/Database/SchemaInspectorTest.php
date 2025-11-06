<?php

use App\Services\Database\SchemaInspector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->inspector = new SchemaInspector;
});

test('schema inspector lists tables', function () {
    DB::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    DB::shouldReceive('getDriverName')
        ->once()
        ->andReturn('mysql');
    DB::shouldReceive('getDatabaseName')
        ->once()
        ->andReturn('test_db');
    DB::shouldReceive('select')
        ->once()
        ->andReturn([
            (object) ['table_name' => 'users', 'table_type' => 'BASE TABLE'],
            (object) ['table_name' => 'posts', 'table_type' => 'BASE TABLE'],
        ]);

    $result = $this->inspector->listTables('mysql');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['tables'])->toHaveCount(2);
    expect($result['tables'][0]['name'])->toBe('users');
    expect($result['tables'][1]['name'])->toBe('posts');
});

test('schema inspector describes table schema', function () {
    Schema::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    Schema::shouldReceive('getColumns')
        ->with('users')
        ->once()
        ->andReturn([
            ['name' => 'id', 'type' => 'integer'],
            ['name' => 'name', 'type' => 'varchar'],
        ]);
    Schema::shouldReceive('getIndexes')
        ->with('users')
        ->once()
        ->andReturn([]);

    $result = $this->inspector->describeTable('users', 'mysql');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['columns'])->toHaveCount(2);
});

test('schema inspector counts table rows', function () {
    DB::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    DB::shouldReceive('table')
        ->with('users')
        ->once()
        ->andReturnSelf();
    DB::shouldReceive('count')
        ->once()
        ->andReturn(42);

    $result = $this->inspector->getTableRowCount('users', 'mysql');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['row_count'])->toBe(42);
});

test('schema inspector gets database size', function () {
    DB::shouldReceive('connection')
        ->with('mysql')
        ->andReturnSelf();
    DB::shouldReceive('getDriverName')
        ->once()
        ->andReturn('mysql');
    DB::shouldReceive('getDatabaseName')
        ->once()
        ->andReturn('test_db');
    DB::shouldReceive('selectOne')
        ->once()
        ->andReturn((object) ['size_mb' => 10.5]);

    $result = $this->inspector->getDatabaseSize('mysql');

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['size'])->toBe('10.5 MB');
});
