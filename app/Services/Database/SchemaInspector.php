<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database Schema Inspector
 *
 * Inspects database schema including tables, columns,
 * indexes, and relationships.
 */
class SchemaInspector
{
    /**
     * List all tables in the database
     */
    public function listTables(?string $connection = null): array
    {
        try {
            $connection = $connection ?? config('database.default');
            $dbType = DB::connection($connection)->getDriverName();

            $tables = match ($dbType) {
                'pgsql' => $this->listPostgresTables($connection),
                'mysql', 'mariadb' => $this->listMySQLTables($connection),
                'sqlite' => $this->listSQLiteTables($connection),
                default => throw new \InvalidArgumentException("Unsupported database type: {$dbType}"),
            };

            return [
                'success' => true,
                'tables' => $tables,
                'count' => count($tables),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get table schema information
     */
    public function describeTable(string $tableName, ?string $connection = null): array
    {
        try {
            $connection = $connection ?? config('database.default');

            $columns = Schema::connection($connection)->getColumns($tableName);
            $indexes = Schema::connection($connection)->getIndexes($tableName);

            return [
                'success' => true,
                'table' => $tableName,
                'columns' => $columns,
                'indexes' => $indexes,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get table row count
     */
    public function getTableRowCount(string $tableName, ?string $connection = null): array
    {
        try {
            $connection = $connection ?? config('database.default');
            $count = DB::connection($connection)->table($tableName)->count();

            return [
                'success' => true,
                'table' => $tableName,
                'row_count' => $count,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get foreign key relationships
     */
    public function getForeignKeys(string $tableName, ?string $connection = null): array
    {
        try {
            $connection = $connection ?? config('database.default');
            $foreignKeys = Schema::connection($connection)->getForeignKeys($tableName);

            return [
                'success' => true,
                'table' => $tableName,
                'foreign_keys' => $foreignKeys,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database size
     */
    public function getDatabaseSize(?string $connection = null): array
    {
        try {
            $connection = $connection ?? config('database.default');
            $dbType = DB::connection($connection)->getDriverName();
            $database = DB::connection($connection)->getDatabaseName();

            $size = match ($dbType) {
                'pgsql' => $this->getPostgresSize($connection, $database),
                'mysql', 'mariadb' => $this->getMySQLSize($connection, $database),
                default => 'Unknown',
            };

            return [
                'success' => true,
                'database' => $database,
                'size' => $size,
                'type' => $dbType,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List PostgreSQL tables
     */
    private function listPostgresTables(string $connection): array
    {
        $results = DB::connection($connection)->select("
            SELECT table_name, table_type
            FROM information_schema.tables
            WHERE table_schema = 'public'
            ORDER BY table_name
        ");

        return array_map(fn ($row) => [
            'name' => $row->table_name,
            'type' => $row->table_type,
        ], $results);
    }

    /**
     * List MySQL tables
     */
    private function listMySQLTables(string $connection): array
    {
        $database = DB::connection($connection)->getDatabaseName();
        $results = DB::connection($connection)->select('
            SELECT table_name, table_type
            FROM information_schema.tables
            WHERE table_schema = ?
            ORDER BY table_name
        ', [$database]);

        return array_map(fn ($row) => [
            'name' => $row->table_name,
            'type' => $row->table_type,
        ], $results);
    }

    /**
     * List SQLite tables
     */
    private function listSQLiteTables(string $connection): array
    {
        $results = DB::connection($connection)->select("
            SELECT name, type
            FROM sqlite_master
            WHERE type = 'table'
            ORDER BY name
        ");

        return array_map(fn ($row) => [
            'name' => $row->name,
            'type' => $row->type,
        ], $results);
    }

    /**
     * Get PostgreSQL database size
     */
    private function getPostgresSize(string $connection, string $database): string
    {
        $result = DB::connection($connection)->selectOne('
            SELECT pg_size_pretty(pg_database_size(?)) as size
        ', [$database]);

        return $result->size ?? 'Unknown';
    }

    /**
     * Get MySQL database size
     */
    private function getMySQLSize(string $connection, string $database): string
    {
        $result = DB::connection($connection)->selectOne('
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = ?
        ', [$database]);

        return ($result->size_mb ?? 0).' MB';
    }
}
