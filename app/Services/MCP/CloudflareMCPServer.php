<?php

namespace App\Services\MCP;

use App\Services\Cloudflare\CloudflareService;
use App\Services\Cloudflare\DNSService;
use App\Services\Cloudflare\TunnelService;

class CloudflareMCPServer
{
    public function __construct(
        private CloudflareService $cloudflare,
        private DNSService $dnsService,
        private TunnelService $tunnelService
    ) {}

    /**
     * List all zones
     */
    public function listZones(array $params = []): array
    {
        try {
            $accountId = config('cloudflare.account_id');
            $result = $this->cloudflare->get("accounts/{$accountId}/zones", $params);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List DNS records for a zone
     */
    public function listDNSRecords(string $zoneId, array $filters = []): array
    {
        try {
            $records = $this->dnsService->listDNSRecords($zoneId, $filters);

            return [
                'success' => true,
                'data' => $records,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new DNS record
     */
    public function createDNSRecord(string $zoneId, array $data): array
    {
        try {
            $record = $this->dnsService->createDNSRecord($zoneId, $data);

            return [
                'success' => true,
                'data' => $record,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update a DNS record
     */
    public function updateDNSRecord(string $zoneId, string $recordId, array $data): array
    {
        try {
            $record = $this->dnsService->updateDNSRecord($zoneId, $recordId, $data);

            return [
                'success' => true,
                'data' => $record,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a DNS record
     */
    public function deleteDNSRecord(string $zoneId, string $recordId): array
    {
        try {
            $success = $this->dnsService->deleteDNSRecord($zoneId, $recordId);

            return [
                'success' => $success,
                'message' => $success ? 'DNS record deleted successfully' : 'Failed to delete DNS record',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List all tunnels
     */
    public function listTunnels(): array
    {
        try {
            $accountId = config('cloudflare.account_id');
            $tunnels = $this->tunnelService->listTunnels($accountId);

            return [
                'success' => true,
                'data' => $tunnels,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new tunnel
     */
    public function createTunnel(string $name, ?string $secret = null): array
    {
        try {
            $accountId = config('cloudflare.account_id');
            $secret = $secret ?? bin2hex(random_bytes(32));
            $tunnel = $this->tunnelService->createTunnel($accountId, $name, $secret);

            return [
                'success' => true,
                'data' => $tunnel,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a tunnel
     */
    public function deleteTunnel(string $tunnelId): array
    {
        try {
            $accountId = config('cloudflare.account_id');
            $success = $this->tunnelService->deleteTunnel($accountId, $tunnelId);

            return [
                'success' => $success,
                'message' => $success ? 'Tunnel deleted successfully' : 'Failed to delete tunnel',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available MCP tools
     */
    public function getTools(): array
    {
        return [
            [
                'name' => 'cloudflare_list_zones',
                'description' => 'List all zones in the Cloudflare account',
                'parameters' => [],
            ],
            [
                'name' => 'cloudflare_list_dns_records',
                'description' => 'List DNS records for a specific zone',
                'parameters' => [
                    'zone_id' => ['type' => 'string', 'required' => true, 'description' => 'The zone ID'],
                    'type' => ['type' => 'string', 'required' => false, 'description' => 'Filter by record type'],
                    'name' => ['type' => 'string', 'required' => false, 'description' => 'Filter by record name'],
                ],
            ],
            [
                'name' => 'cloudflare_create_dns_record',
                'description' => 'Create a new DNS record',
                'parameters' => [
                    'zone_id' => ['type' => 'string', 'required' => true],
                    'type' => ['type' => 'string', 'required' => true, 'description' => 'Record type (A, AAAA, CNAME, etc.)'],
                    'name' => ['type' => 'string', 'required' => true, 'description' => 'Record name'],
                    'content' => ['type' => 'string', 'required' => true, 'description' => 'Record content/value'],
                    'ttl' => ['type' => 'integer', 'required' => false, 'description' => 'Time to live (default: 1 for auto)'],
                    'proxied' => ['type' => 'boolean', 'required' => false, 'description' => 'Whether to proxy through Cloudflare'],
                ],
            ],
            [
                'name' => 'cloudflare_update_dns_record',
                'description' => 'Update an existing DNS record',
                'parameters' => [
                    'zone_id' => ['type' => 'string', 'required' => true],
                    'record_id' => ['type' => 'string', 'required' => true],
                    'type' => ['type' => 'string', 'required' => false],
                    'name' => ['type' => 'string', 'required' => false],
                    'content' => ['type' => 'string', 'required' => false],
                    'ttl' => ['type' => 'integer', 'required' => false],
                    'proxied' => ['type' => 'boolean', 'required' => false],
                ],
            ],
            [
                'name' => 'cloudflare_delete_dns_record',
                'description' => 'Delete a DNS record',
                'parameters' => [
                    'zone_id' => ['type' => 'string', 'required' => true],
                    'record_id' => ['type' => 'string', 'required' => true],
                ],
            ],
            [
                'name' => 'cloudflare_list_tunnels',
                'description' => 'List all Cloudflare Tunnels',
                'parameters' => [],
            ],
            [
                'name' => 'cloudflare_create_tunnel',
                'description' => 'Create a new Cloudflare Tunnel',
                'parameters' => [
                    'name' => ['type' => 'string', 'required' => true, 'description' => 'Tunnel name'],
                    'secret' => ['type' => 'string', 'required' => false, 'description' => 'Tunnel secret (generated if not provided)'],
                ],
            ],
            [
                'name' => 'cloudflare_delete_tunnel',
                'description' => 'Delete a Cloudflare Tunnel',
                'parameters' => [
                    'tunnel_id' => ['type' => 'string', 'required' => true],
                ],
            ],
        ];
    }

    /**
     * Execute an MCP tool
     */
    public function executeTool(string $toolName, array $parameters): array
    {
        return match ($toolName) {
            'cloudflare_list_zones' => $this->listZones($parameters),
            'cloudflare_list_dns_records' => $this->listDNSRecords($parameters['zone_id'] ?? '', $parameters),
            'cloudflare_create_dns_record' => $this->createDNSRecord($parameters['zone_id'] ?? '', $parameters),
            'cloudflare_update_dns_record' => $this->updateDNSRecord(
                $parameters['zone_id'] ?? '',
                $parameters['record_id'] ?? '',
                $parameters
            ),
            'cloudflare_delete_dns_record' => $this->deleteDNSRecord(
                $parameters['zone_id'] ?? '',
                $parameters['record_id'] ?? ''
            ),
            'cloudflare_list_tunnels' => $this->listTunnels(),
            'cloudflare_create_tunnel' => $this->createTunnel(
                $parameters['name'] ?? '',
                $parameters['secret'] ?? null
            ),
            'cloudflare_delete_tunnel' => $this->deleteTunnel($parameters['tunnel_id'] ?? ''),
            default => [
                'success' => false,
                'error' => "Unknown tool: {$toolName}",
            ],
        };
    }
}
