<?php

namespace App\Services\Cloudflare;

class DNSService
{
    public function __construct(
        private CloudflareService $cloudflare
    ) {}

    /**
     * List DNS records for a zone
     */
    public function listDNSRecords(string $zoneId, array $filters = []): array
    {
        $endpoint = "zones/{$zoneId}/dns_records";

        return $this->cloudflare->get($endpoint, $filters);
    }

    /**
     * Get a specific DNS record
     */
    public function getDNSRecord(string $zoneId, string $recordId): array
    {
        $endpoint = "zones/{$zoneId}/dns_records/{$recordId}";

        return $this->cloudflare->get($endpoint);
    }

    /**
     * Create a new DNS record
     */
    public function createDNSRecord(string $zoneId, array $data): array
    {
        $endpoint = "zones/{$zoneId}/dns_records";

        return $this->cloudflare->post($endpoint, $data);
    }

    /**
     * Update an existing DNS record
     */
    public function updateDNSRecord(string $zoneId, string $recordId, array $data): array
    {
        $endpoint = "zones/{$zoneId}/dns_records/{$recordId}";

        return $this->cloudflare->put($endpoint, $data);
    }

    /**
     * Delete a DNS record
     */
    public function deleteDNSRecord(string $zoneId, string $recordId): bool
    {
        $endpoint = "zones/{$zoneId}/dns_records/{$recordId}";

        try {
            $this->cloudflare->delete($endpoint);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
