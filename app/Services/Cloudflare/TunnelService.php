<?php

namespace App\Services\Cloudflare;

class TunnelService
{
    public function __construct(
        private CloudflareService $cloudflare
    ) {}

    /**
     * List all tunnels for an account
     */
    public function listTunnels(string $accountId): array
    {
        $endpoint = "accounts/{$accountId}/cfd_tunnel";

        return $this->cloudflare->get($endpoint);
    }

    /**
     * Get a specific tunnel
     */
    public function getTunnel(string $accountId, string $tunnelId): array
    {
        $endpoint = "accounts/{$accountId}/cfd_tunnel/{$tunnelId}";

        return $this->cloudflare->get($endpoint);
    }

    /**
     * Create a new tunnel
     */
    public function createTunnel(string $accountId, string $name, string $secret): array
    {
        $endpoint = "accounts/{$accountId}/cfd_tunnel";

        return $this->cloudflare->post($endpoint, [
            'name' => $name,
            'tunnel_secret' => base64_encode($secret),
            'config_src' => 'cloudflare',
        ]);
    }

    /**
     * Delete a tunnel
     */
    public function deleteTunnel(string $accountId, string $tunnelId): bool
    {
        $endpoint = "accounts/{$accountId}/cfd_tunnel/{$tunnelId}";

        try {
            $this->cloudflare->delete($endpoint);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get tunnel token
     */
    public function getTunnelToken(string $accountId, string $tunnelId): string
    {
        $endpoint = "accounts/{$accountId}/cfd_tunnel/{$tunnelId}/token";

        $result = $this->cloudflare->get($endpoint);

        return $result['token'] ?? '';
    }
}
