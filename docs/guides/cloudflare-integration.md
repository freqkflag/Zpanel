# Cloudflare Integration Guide

Comprehensive guide to Zpanel's Cloudflare integration for DNS, Tunnels, SSL, and more.

## Overview

Zpanel integrates deeply with Cloudflare to provide:
- 🌐 Automated DNS management
- 🔒 SSL/TLS certificate automation
- 🛡️ DDoS protection configuration
- 🚇 Cloudflare Tunnel automation
- 📊 Analytics and insights
- ⚡ Workers deployment (planned)

## Prerequisites

- Cloudflare account
- Domain added to Cloudflare
- Cloudflare API token with required permissions

## Setup

### 1. Create Cloudflare API Token

1. Log in to [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Navigate to **My Profile** → **API Tokens**
3. Click **Create Token**
4. Use **Edit zone DNS** template or create custom token
5. **Required permissions:**
   - Zone - DNS - Edit
   - Zone - Zone - Read
   - Account - Cloudflare Tunnel - Edit (for tunnels)
6. Copy the token (shown only once)

### 2. Configure Zpanel

Add to `.env`:

```env
CLOUDFLARE_API_TOKEN=your-api-token-here
CLOUDFLARE_ACCOUNT_ID=your-account-id
CLOUDFLARE_ZONE_ID=your-default-zone-id
```

### 3. Enable Cloudflare Integration

Navigate to **Settings** → **Integrations** → **Cloudflare** and enable the integration.

## Features

### DNS Management

#### Automatic DNS Records

When you deploy an application with a custom domain, Zpanel can automatically:

1. Create DNS A/AAAA record pointing to your server
2. Configure SSL certificate (Let's Encrypt or Cloudflare)
3. Set up proxy (if desired)

**Example:**
```
Application domain: app.example.com
Server IP: 192.168.1.100
→ Creates: A record app.example.com → 192.168.1.100
```

#### Manual DNS Management

**Via UI:**
1. Navigate to **Domains** → **DNS Records**
2. Click **Add Record**
3. Enter details (type, name, content)
4. Save

**Via API:**
```bash
curl -X POST https://your-domain.com/api/v1/cloudflare/dns \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "zone_id": "zone-id",
    "type": "A",
    "name": "app",
    "content": "192.168.1.100",
    "proxied": true
  }'
```

#### Bulk DNS Operations

Import DNS records from file:

```bash
# dns-records.json
[
  {"type": "A", "name": "app1", "content": "192.168.1.100"},
  {"type": "A", "name": "app2", "content": "192.168.1.101"},
  {"type": "CNAME", "name": "www", "content": "app.example.com"}
]
```

### Cloudflare Tunnels

#### What are Tunnels?

Cloudflare Tunnels create secure connections from your servers to Cloudflare without opening inbound ports.

**Benefits:**
- No open ports (no firewall config needed)
- Automatic SSL
- DDoS protection
- Global load balancing

#### Creating a Tunnel

**Via UI:**
1. Navigate to **Cloudflare** → **Tunnels**
2. Click **Create Tunnel**
3. Enter tunnel name
4. Select server to install connector
5. Configure routes

**Via API:**
```bash
curl -X POST https://your-domain.com/api/v1/cloudflare/tunnels \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "production-tunnel",
    "server_id": 1
  }'
```

#### Tunnel Configuration

Zpanel automatically:
1. Installs cloudflared on server
2. Creates tunnel credentials
3. Configures ingress rules
4. Starts tunnel service
5. Monitors tunnel health

**Example ingress config:**
```yaml
tunnel: <TUNNEL_ID>
credentials-file: /root/.cloudflared/<TUNNEL_ID>.json

ingress:
  - hostname: app.example.com
    service: http://localhost:3000
  - hostname: api.example.com
    service: http://localhost:8080
  - service: http_status:404
```

### SSL/TLS Management

#### Automatic SSL

When deploying with custom domain:

1. **Let's Encrypt** (default) - Free SSL via certbot
2. **Cloudflare** - Proxied through Cloudflare (automatic)
3. **Custom** - Upload your own certificate

#### SSL Configuration

**Via UI:**
1. Navigate to application settings
2. Select **SSL/TLS** tab
3. Choose SSL provider
4. Configure auto-renewal

**Cloudflare SSL Options:**
- **Flexible**: Cloudflare ↔ User (encrypted), Cloudflare ↔ Server (unencrypted)
- **Full**: Both encrypted, but server uses self-signed cert
- **Full (Strict)**: Both encrypted, server uses valid cert
- **Off**: No encryption

### DDoS Protection

#### Basic Protection

Enabled automatically when using Cloudflare proxy.

**Features:**
- Automatic DDoS mitigation
- Rate limiting
- Bot detection
- Challenge pages

#### Advanced Configuration

**Via UI:**
1. Navigate to **Cloudflare** → **Security**
2. Configure **Rate Limiting**
3. Set up **WAF Rules**
4. Enable **Bot Fight Mode**

**Example Rate Limit:**
```
Path: /api/*
Rate: 100 requests per minute
Action: Block
Duration: 1 hour
```

### Analytics

#### Traffic Analytics

View in **Cloudflare** → **Analytics**:

- Request volume
- Bandwidth usage
- Response codes
- Geographic distribution
- Top URLs

#### Integration with Zpanel

Zpanel fetches and displays Cloudflare analytics:

```bash
# Via API
curl https://your-domain.com/api/v1/cloudflare/analytics?zone_id=xxx&period=24h \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Workers (Planned)

Deploy Cloudflare Workers directly from Zpanel:

**Features (coming soon):**
- Worker code deployment
- KV namespace management
- Route configuration
- Durable Objects
- Workers analytics

## MCP Integration

Zpanel includes a **Cloudflare MCP Server** for AI-assisted management.

### Available MCP Tools

- `list_dns_records` - List DNS records for zone
- `create_dns_record` - Create new DNS record
- `update_dns_record` - Update existing DNS record
- `delete_dns_record` - Delete DNS record
- `list_tunnels` - List Cloudflare Tunnels
- `create_tunnel` - Create new tunnel
- `get_analytics` - Fetch analytics data

### Using with Cursor/Claude

The MCP server enables natural language operations:

```
"Create an A record for api.example.com pointing to 192.168.1.100"
→ MCP executes create_dns_record with appropriate parameters

"Show me DNS records for example.com"
→ MCP executes list_dns_records and formats results
```

## Automation Workflows

### Automatic Domain Configuration

When deploying a new application:

1. **User enters** domain: `app.example.com`
2. **Zpanel checks** if domain uses Cloudflare
3. **If yes**, prompts to auto-configure
4. **Creates** DNS record
5. **Configures** SSL
6. **Sets up** proxy if desired

### Tunnel Auto-Configuration

For servers behind NAT or firewall:

1. **Detect** restricted network
2. **Suggest** Cloudflare Tunnel
3. **Auto-install** cloudflared
4. **Create** tunnel
5. **Configure** routing
6. **Monitor** health

## Security Best Practices

### API Token Management

- ✅ Use scoped tokens (minimum permissions)
- ✅ Rotate tokens regularly
- ✅ Store tokens encrypted
- ✅ Audit token usage
- ❌ Never commit tokens to Git
- ❌ Don't share tokens across environments

### DNS Security

- Enable **DNSSEC** on Cloudflare
- Use **CAA records** to restrict certificate issuance
- Enable **DNSSEC** on Cloudflare
- Monitor DNS changes

### Tunnel Security

- Use **unique tunnels** per environment
- Rotate **tunnel credentials** periodically
- Monitor **tunnel access logs**
- Restrict **ingress rules** to specific services

## Troubleshooting

### DNS Not Updating

**Check:**
```bash
# Verify API token permissions
curl https://api.cloudflare.com/client/v4/user/tokens/verify \
  -H "Authorization: Bearer YOUR_TOKEN"

# Check Zpanel logs
tail -f implementation/phase-1/Zpanel/storage/logs/laravel.log | grep cloudflare
```

### Tunnel Connection Issues

**Debug:**
```bash
# Check cloudflared status
systemctl status cloudflared

# View tunnel logs
journalctl -u cloudflared -f

# Test tunnel connectivity
cloudflared tunnel info <TUNNEL_ID>
```

### SSL Certificate Errors

**Solutions:**
1. Verify domain points to Cloudflare nameservers
2. Check SSL mode in Cloudflare (use Full or Full Strict)
3. Ensure origin server has valid certificate
4. Check Cloudflare cache

## Performance Tips

### Caching

Configure Cloudflare caching:

**Page Rules:**
```
URL: example.com/static/*
Settings:
  - Cache Level: Cache Everything
  - Edge Cache TTL: 1 month
```

### Optimization

- **Enable Brotli** compression
- **Enable HTTP/3** (QUIC)
- **Minify** HTML, CSS, JS
- **Enable Auto Minify**
- Use **Cloudflare CDN** for assets

## Cost Optimization

### Free Tier Usage

Maximize Cloudflare Free tier:

- ✅ Unlimited bandwidth
- ✅ Unlimited DNS queries
- ✅ Basic DDoS protection
- ✅ Universal SSL
- ✅ Limited Workers (100k requests/day)

### Paid Features

Consider upgrading for:
- Advanced DDoS protection
- More Workers requests
- Image optimization
- Load balancing
- Advanced WAF

## Implementation Details

See [MCP Server Enhancement](../../implementation/phase-1/MCP-server-enhancement.md) for technical implementation.

## API Reference

### Cloudflare Service

Located at: `app/Services/Cloudflare/CloudflareService.php`

**Methods:**
- `listDNSRecords(string $zoneId): array`
- `createDNSRecord(string $zoneId, array $data): object`
- `updateDNSRecord(string $zoneId, string $recordId, array $data): object`
- `deleteDNSRecord(string $zoneId, string $recordId): bool`
- `listTunnels(): array`
- `createTunnel(string $name): object`
- `deleteTunnel(string $tunnelId): bool`

## Additional Resources

- [Cloudflare API Documentation](https://developers.cloudflare.com/api/)
- [Cloudflare Tunnels Documentation](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/)
- [Cloudflare MCP Documentation](https://developers.cloudflare.com/agents/model-context-protocol/)
- [Technical Roadmap](../../research/technical-integration-roadmap.md)

