<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Database Query Executor
 *
 * Safely executes queries across multiple database types
 * with proper validation and error handling.
 */
class QueryExecutor
{
    /**
     * Execute a read query (SELECT)
     */
    public function executeRead(string $connection, string $query, array $bindings = []): array
    {
        try {
            // Validate it's a read query
            if (! $this->isReadQuery($query)) {
                throw new \InvalidArgumentException('Only SELECT queries are allowed in executeRead');
            }

            $results = DB::connection($connection)->select($query, $bindings);

            return [
                'success' => true,
                'rows' => $results,
                'count' => count($results),
            ];
        } catch (\Exception $e) {
            Log::error('Query execution error', [
                'connection' => $connection,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute a write query (INSERT, UPDATE, DELETE)
     */
    public function executeWrite(string $connection, string $query, array $bindings = []): array
    {
        try {
            // Validate it's a write query
            if ($this->isReadQuery($query)) {
                throw new \InvalidArgumentException('Use executeRead for SELECT queries');
            }

            // Prevent dangerous operations
            if ($this->isDangerousQuery($query)) {
                throw new \InvalidArgumentException('Dangerous query operations are not allowed');
            }

            $affected = DB::connection($connection)->affectingStatement($query, $bindings);

            return [
                'success' => true,
                'affected_rows' => $affected,
            ];
        } catch (\Exception $e) {
            Log::error('Write query execution error', [
                'connection' => $connection,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute multiple queries in a transaction
     */
    public function executeTransaction(string $connection, array $queries): array
    {
        try {
            DB::connection($connection)->transaction(function () use ($queries, $connection) {
                foreach ($queries as $queryData) {
                    $query = $queryData['query'];
                    $bindings = $queryData['bindings'] ?? [];

                    if ($this->isReadQuery($query)) {
                        DB::connection($connection)->select($query, $bindings);
                    } else {
                        DB::connection($connection)->statement($query, $bindings);
                    }
                }
            });

            return [
                'success' => true,
                'queries_executed' => count($queries),
            ];
        } catch (\Exception $e) {
            Log::error('Transaction execution error', [
                'connection' => $connection,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get query explanation (EXPLAIN)
     */
    public function explainQuery(string $connection, string $query, array $bindings = []): array
    {
        try {
            $dbType = DB::connection($connection)->getDriverName();

            $explainQuery = match ($dbType) {
                'pgsql' => "EXPLAIN (FORMAT JSON) {$query}",
                'mysql', 'mariadb' => "EXPLAIN FORMAT=JSON {$query}",
                'sqlite' => "EXPLAIN QUERY PLAN {$query}",
                default => throw new \InvalidArgumentException("Unsupported database type: {$dbType}"),
            };

            $results = DB::connection($connection)->select($explainQuery, $bindings);

            return [
                'success' => true,
                'explain' => $results,
                'database_type' => $dbType,
            ];
        } catch (\Exception $e) {
            Log::error('Query explain error', [
                'connection' => $connection,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if query is a read query
     */
    private function isReadQuery(string $query): bool
    {
        $query = trim(strtoupper($query));

        return str_starts_with($query, 'SELECT')
            || str_starts_with($query, 'SHOW')
            || str_starts_with($query, 'DESCRIBE')
            || str_starts_with($query, 'EXPLAIN');
    }

    /**
     * Check if query contains dangerous operations
     */
    private function isDangerousQuery(string $query): bool
    {
        $query = strtoupper($query);
        $dangerous = ['DROP DATABASE', 'DROP SCHEMA', 'TRUNCATE TABLE', 'DELETE FROM'];

        foreach ($dangerous as $operation) {
            if (str_contains($query, $operation)) {
                // Allow if has WHERE clause
                if ($operation === 'DELETE FROM' && str_contains($query, 'WHERE')) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Validate query syntax
     */
    public function validateQuery(string $connection, string $query): array
    {
        try {
            // Try to prepare the query
            $pdo = DB::connection($connection)->getPdo();
            $stmt = $pdo->prepare($query);

            return [
                'valid' => true,
                'message' => 'Query syntax is valid',
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
