# MCP Server Integration Guide

Model Context Protocol (MCP) integration in Zpanel enables AI-powered development workflows and external service integrations.

## What is MCP?

MCP (Model Context Protocol) is a protocol for integrating AI assistants with development tools and external services.

## Current MCP Integration

Zpanel inherits MCP support from Coolify via **Laravel Boost**.

### Existing Configuration

`.mcp.json` in project root:
```json
{
  "mcpServers": {
    "laravel-boost": {
      "command": "php",
      "args": ["artisan", "boost:mcp"]
    }
  }
}
```

## Enhanced MCP Framework (Planned)

Zpanel will extend MCP support to include multiple servers.

### Planned MCP Servers

1. **Cloudflare MCP Server** - DNS, Tunnel, SSL management
2. **GitHub MCP Server** - Repository management
3. **Database MCP Server** - Database operations
4. **Docker MCP Server** - Container management
5. **Custom MCP Servers** - User-defined integrations

## Implementation Guide

See [MCP Server Enhancement](../../implementation/phase-1/MCP-server-enhancement.md) for detailed implementation steps.

## Using MCP Servers

### Via Cursor IDE

1. Ensure `.mcp.json` is in project root
2. Restart Cursor IDE
3. MCP servers auto-connect
4. Use via AI assistant

### Via API

```bash
# List MCP servers
curl https://your-domain.com/api/v1/mcp/servers \
  -H "Authorization: Bearer YOUR_TOKEN"

# Execute MCP tool
curl -X POST https://your-domain.com/api/v1/mcp/execute \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "server": "cloudflare",
    "tool": "list_dns_records",
    "params": {"zone_id": "abc123"}
  }'
```

## Cloudflare MCP Integration

### Features

- List/Create/Update/Delete DNS records
- Manage Cloudflare Tunnels
- SSL certificate management
- WAF rule configuration
- Analytics queries

### Configuration

```env
CLOUDFLARE_API_TOKEN=your-cloudflare-api-token
CLOUDFLARE_ACCOUNT_ID=your-account-id
CLOUDFLARE_ZONE_ID=your-zone-id
```

### Example Operations

```python
# Using MCP to manage DNS
from mcp_client import MCPClient

client = MCPClient(server="cloudflare")

# List DNS records
records = client.execute("list_dns_records", {
    "zone_id": "your-zone-id"
})

# Create DNS record
client.execute("create_dns_record", {
    "zone_id": "your-zone-id",
    "type": "A",
    "name": "app",
    "content": "192.168.1.100"
})
```

## Security

- **Token-based** authentication for MCP access
- **Encrypted** API credentials
- **Team-scoped** permissions
- **Audit logging** for MCP operations

## Additional Resources

- [MCP Protocol Specification](https://spec.modelcontextprotocol.io/)
- [Laravel Boost MCP](https://github.com/laravel/boost)
- [Cloudflare MCP Documentation](https://developers.cloudflare.com/agents/model-context-protocol/)

