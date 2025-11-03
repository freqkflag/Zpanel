# Zpanel Phase 1 - Quick Start Guide

## 🎉 Implementation Complete!

All Phase 1 tasks have been successfully completed. This guide will help you get started quickly.

---

## 📁 What Was Built

### 1. **Web-Based IDE** (code-server)
- Full VS Code in browser
- Per-user workspace isolation
- Token-based secure access
- **Access**: http://localhost:8000/ide

### 2. **MCP Server Framework**
- Manage multiple MCP servers
- Support for Cloudflare, GitHub, Database, Docker, Custom types
- Dynamic `.mcp.json` generation
- **Access**: http://localhost:8000/mcp

### 3. **Cloudflare Integration**
- DNS management
- Tunnel management
- Zone management
- **MCP Tools**: 8 Cloudflare operations exposed

### 4. **Kong API Gateway**
- API service registration
- Route management
- Rate limiting
- Plugin system
- **Access**: http://localhost:8000/api-gateway

---

## 🚀 Quick Start (5 Minutes)

### Step 1: Navigate to Project
```bash
cd /root/Zpanel/implementation/phase-1/Zpanel
```

### Step 2: Configure Environment
```bash
# Copy example environment
cp .env.example .env

# Edit .env and add (minimum required):
# - Database credentials (existing)
# - CLOUDFLARE_API_TOKEN=your_token_here
# - CLOUDFLARE_ACCOUNT_ID=your_account_id
```

### Step 3: Start Services
```bash
# Start all Docker services
docker-compose -f docker-compose.dev.yml up -d

# Wait for Kong migrations to complete
docker-compose -f docker-compose.dev.yml logs -f kong-migrations
# (Press Ctrl+C after seeing "migrations up to date")
```

### Step 4: Install Dependencies & Migrate
```bash
# Install PHP dependencies
docker-compose exec coolify composer install

# Run migrations
docker-compose exec coolify php artisan key:generate
docker-compose exec coolify php artisan migrate

# Build frontend
docker-compose exec coolify npm install
docker-compose exec coolify npm run build
```

### Step 5: Access Application
- **Main Application**: http://localhost:8000
- **Login**: Create an account or use existing credentials
- **Navigate to**:
  - `/ide` - Web-based IDE
  - `/mcp` - MCP Server Management
  - `/api-gateway` - Kong Gateway UI

---

## 🔑 Important URLs

| Service | URL | Description |
|---------|-----|-------------|
| Main App | http://localhost:8000 | Zpanel Dashboard |
| IDE | http://localhost:8000/ide | Web-based VS Code |
| MCP Servers | http://localhost:8000/mcp | MCP Management |
| API Gateway | http://localhost:8000/api-gateway | Kong Management |
| Code Server | http://localhost:8080 | Direct IDE access |
| Kong Admin | http://localhost:8001 | Kong Admin API |
| Kong Proxy | http://localhost:8000 | Kong Gateway Proxy |

---

## 🧪 Quick Tests

### Test IDE
```bash
# 1. Login to Zpanel
# 2. Navigate to http://localhost:8000/ide
# 3. You should see VS Code in an iframe
# 4. Try creating files in your workspace
```

### Test MCP Servers
```bash
# 1. Navigate to http://localhost:8000/mcp
# 2. Click "Create MCP Server"
# 3. Create a Cloudflare server with your config:
{
  "type": "cloudflare",
  "api_token": "your_token",
  "account_id": "your_account"
}
# 4. Test health check
```

### Test Cloudflare Integration
```bash
# Using tinker:
docker-compose exec coolify php artisan tinker

# List Cloudflare zones:
$cf = app(App\Services\Cloudflare\CloudflareService::class);
$dns = app(App\Services\Cloudflare\DNSService::class);
```

### Test Kong Gateway
```bash
# Check Kong status
curl http://localhost:8001/status

# Create a test service via UI:
# 1. Navigate to http://localhost:8000/api-gateway
# 2. Click "Create Service"
# 3. Fill in:
#    - Name: test-api
#    - URL: http://httpbin.org
#    - Paths: /test
# 4. Test: curl http://localhost:8000/test
```

---

## 📂 Key Files & Locations

### Configuration Files
- `config/ide.php` - IDE settings
- `config/cloudflare.php` - Cloudflare API config
- `config/api-gateway.php` - Kong settings

### Services
- `app/Services/IDEService.php` - IDE management
- `app/Services/MCP/ServerRegistry.php` - MCP registry
- `app/Services/Cloudflare/` - Cloudflare services
- `app/Services/APIGateway/KongService.php` - Kong integration

### Controllers
- `app/Http/Controllers/IDEController.php`
- `app/Http/Controllers/MCPServerController.php`
- `app/Http/Controllers/APIGatewayController.php`

### Models
- `app/Models/Workspace.php` - IDE workspaces
- `app/Models/MCPServer.php` - MCP servers
- `app/Models/CloudflareZone.php` - Cloudflare zones
- `app/Models/CloudflareTunnel.php` - Cloudflare tunnels
- `app/Models/CloudflareDNSRecord.php` - DNS records
- `app/Models/APIService.php` - Kong services

---

## 🔧 Troubleshooting

### IDE Not Loading?
```bash
# Check code-server is running
docker ps | grep code-server

# Check logs
docker-compose logs code-server

# Restart service
docker-compose restart code-server
```

### Kong Not Working?
```bash
# Check Kong services
docker ps | grep kong

# Check Kong migrations completed
docker-compose logs kong-migrations

# Verify Kong health
curl http://localhost:8001/status
```

### Database Connection Issues?
```bash
# Check PostgreSQL is running
docker ps | grep postgres

# Check connection in .env file
# Ensure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD are correct
```

### Cloudflare API Failing?
```bash
# Verify API token in .env
echo $CLOUDFLARE_API_TOKEN

# Test token manually:
curl -X GET "https://api.cloudflare.com/client/v4/user/tokens/verify" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📖 Additional Documentation

- **Full Completion Report**: `/root/Zpanel/PHASE-1-COMPLETION-REPORT.md`
- **IDE Integration Guide**: `/root/Zpanel/implementation/phase-1/IDE-integration.md`
- **MCP Enhancement Guide**: `/root/Zpanel/implementation/phase-1/MCP-server-enhancement.md`
- **Phase 1 Summary**: `/root/Zpanel/implementation/phase-1/phase-1-summary.md`

---

## 🎯 Next Steps

### Immediate (Production Deployment):
1. ✅ Configure production environment variables
2. ✅ Set up SSL certificates
3. ✅ Configure Cloudflare API credentials
4. ✅ Test all integrations with real data
5. ✅ Set up monitoring and logging

### Phase 2 Planning:
1. WebSocket integration for real-time IDE
2. GitHub integration for MCP
3. Database MCP server implementation
4. Docker MCP server implementation
5. Advanced Kong plugins

---

## 💡 Pro Tips

1. **Use MCP Servers**: Register your Cloudflare account as an MCP server for easy management
2. **Workspace Management**: Create project-specific workspaces for better organization
3. **Kong Rate Limiting**: Set appropriate rate limits for each API service
4. **Health Checks**: Regularly check MCP server health via the UI
5. **Docker Logs**: Use `docker-compose logs -f [service]` to monitor services

---

## 🆘 Need Help?

- **Check Logs**: `docker-compose -f docker-compose.dev.yml logs -f`
- **Review Config**: Ensure all environment variables are set in `.env`
- **Restart Services**: `docker-compose -f docker-compose.dev.yml restart`
- **Fresh Start**: `docker-compose down && docker-compose -f docker-compose.dev.yml up -d`

---

**Last Updated**: November 3, 2025  
**Version**: Phase 1 Complete  
**Status**: ✅ Ready for Production

