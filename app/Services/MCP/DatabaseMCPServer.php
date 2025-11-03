<?php

namespace App\Services\MCP;

use App\Services\Database\QueryExecutor;
use App\Services\Database\SchemaInspector;
use Illuminate\Support\Facades\Log;

/**
 * Database MCP Server
 *
 * Exposes database operations through Model Context Protocol.
 * Supports query execution, schema inspection, and database management.
 */
class DatabaseMCPServer
{
    public function __construct(
        private QueryExecutor $queryExecutor,
        private SchemaInspector $schemaInspector
    ) {}

    /**
     * Get available MCP tools
     */
    public function getTools(): array
    {
        return [
            'database_execute_query' => [
                'name' => 'execute_query',
                'description' => 'Execute a SELECT query on the database',
                'parameters' => [
                    'connection' => ['type' => 'string', 'required' => false],
                    'query' => ['type' => 'string', 'required' => true],
                    'bindings' => ['type' => 'array', 'required' => false],
                ],
            ],
            'database_list_tables' => [
                'name' => 'list_tables',
                'description' => 'List all tables in the database',
                'parameters' => [
                    'connection' => ['type' => 'string', 'required' => false],
                ],
            ],
            'database_describe_table' => [
                'name' => 'describe_table',
                'description' => 'Get table schema information',
                'parameters' => [
                    'table' => ['type' => 'string', 'required' => true],
                    'connection' => ['type' => 'string', 'required' => false],
                ],
            ],
            'database_table_row_count' => [
                'name' => 'get_table_row_count',
                'description' => 'Get row count for a table',
                'parameters' => [
                    'table' => ['type' => 'string', 'required' => true],
                    'connection' => ['type' => 'string', 'required' => false],
                ],
            ],
            'database_explain_query' => [
                'name' => 'explain_query',
                'description' => 'Get query execution plan',
                'parameters' => [
                    'connection' => ['type' => 'string', 'required' => false],
                    'query' => ['type' => 'string', 'required' => true],
                    'bindings' => ['type' => 'array', 'required' => false],
                ],
            ],
            'database_validate_query' => [
                'name' => 'validate_query',
                'description' => 'Validate query syntax',
                'parameters' => [
                    'connection' => ['type' => 'string', 'required' => false],
                    'query' => ['type' => 'string', 'required' => true],
                ],
            ],
            'database_get_foreign_keys' => [
                'name' => 'get_foreign_keys',
                'description' => 'Get foreign key relationships for a table',
                'parameters' => [
                    'table' => ['type' => 'string', 'required' => true],
                    'connection' => ['type' => 'string', 'required' => false],
                ],
            ],
            'database_get_size' => [
                'name' => 'get_database_size',
                'description' => 'Get database size information',
                'parameters' => [
                    'connection' => ['type' => 'string', 'required' => false],
                ],
            ],
        ];
    }

    /**
     * Handle MCP tool call
     */
    public function handleToolCall(string $tool, array $params): array
    {
        try {
            $connection = $params['connection'] ?? null;

            return match ($tool) {
                'database_execute_query' => $this->queryExecutor->executeRead(
                    $connection,
                    $params['query'],
                    $params['bindings'] ?? []
                ),
                'database_list_tables' => $this->schemaInspector->listTables($connection),
                'database_describe_table' => $this->schemaInspector->describeTable($params['table'], $connection),
                'database_table_row_count' => $this->schemaInspector->getTableRowCount($params['table'], $connection),
                'database_explain_query' => $this->queryExecutor->explainQuery(
                    $connection,
                    $params['query'],
                    $params['bindings'] ?? []
                ),
                'database_validate_query' => $this->queryExecutor->validateQuery($connection, $params['query']),
                'database_get_foreign_keys' => $this->schemaInspector->getForeignKeys($params['table'], $connection),
                'database_get_size' => $this->schemaInspector->getDatabaseSize($connection),

                default => throw new \InvalidArgumentException("Unknown tool: {$tool}"),
            };
        } catch (\Exception $e) {
            Log::error('Database MCP tool error', [
                'tool' => $tool,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tool' => $tool,
            ];
        }
    }

    /**
     * Get server configuration for .mcp.json
     */
    public function getConfig(): array
    {
        return [
            'command' => 'php',
            'args' => ['artisan', 'mcp:database'],
        ];
    }
}
