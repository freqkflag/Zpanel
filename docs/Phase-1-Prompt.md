 Complete Zpanel Phase 1 Implementation

## Project Context

**Project Name**: Zpanel  
**Base Repository**: Coolify (forked from https://github.com/coollabsio/coolify)  
**Project Repository**: https://github.com/freqkflag/Zpanel  
**Working Directory**: `/root/Zpanel/implementation/phase-1/Zpanel`  
**Technology Stack**: Laravel 12.20 (PHP 8.4), Livewire 3.6.4, PostgreSQL 15, Redis 7, Docker  
**Current Phase**: Phase 1 - Foundation & Setup (Months 1-3)  

## Mission Statement

Complete all Phase 1 implementation tasks for Zpanel, transforming Coolify into a comprehensive self-hosted control panel with integrated IDE, enhanced MCP server framework, Cloudflare integration, and API Gateway capabilities.

## Phase 1 Objectives

### 1️⃣ **IDE Integration (code-server)**
Integrate a web-based VS Code IDE (code-server) into the control panel for direct code editing capabilities.

### 2️⃣ **MCP Server Framework Enhancement**
Extend the existing Laravel Boost MCP integration to support multiple MCP servers (Cloudflare, GitHub, Database, Docker, Custom).

### 3️⃣ **Cloudflare API Client Implementation**
Build a comprehensive Cloudflare API client for DNS, Tunnels, SSL/TLS, WAF, and Workers management.

### 4️⃣ **Cloudflare MCP Server**
Create an MCP server that exposes Cloudflare operations through the Model Context Protocol.

### 5️⃣ **Basic API Gateway Integration**
Integrate Kong API Gateway for API management, rate limiting, and routing.

---

## 📋 Detailed Implementation Tasks

### **Task Group 1: Development Environment Setup**

#### 1.1 Verify Repository State
- [ ] Navigate to `/root/Zpanel/implementation/phase-1/Zpanel`
- [ ] Verify git repository is properly initialized
- [ ] Check current branch (should be `main`)
- [ ] Review existing `.mcp.json` configuration
- [ ] Identify existing MCP implementation (Laravel Boost)

#### 1.2 Review Existing Architecture
- [ ] Analyze current Laravel application structure
- [ ] Document existing models in `app/Models/`
- [ ] Review current routing in `routes/web.php` and `routes/api.php`
- [ ] Examine Docker setup in `docker-compose.yml` and `docker-compose.dev.yml`
- [ ] Review existing services in `app/Services/`
- [ ] Check database migrations in `database/migrations/`

#### 1.3 Setup Development Environment
- [ ] Verify Docker and Docker Compose are installed
- [ ] Copy `.env.development.example` to `.env` if not exists
- [ ] Update environment variables for development
- [ ] Start Docker services: `docker-compose -f docker-compose.dev.yml up -d`
- [ ] Install PHP dependencies: `docker-compose exec app composer install`
- [ ] Install Node dependencies: `docker-compose exec app npm install`
- [ ] Run migrations: `docker-compose exec app php artisan migrate`
- [ ] Seed database: `docker-compose exec app php artisan db:seed`
- [ ] Verify application is accessible at `http://localhost:8000`

---

### **Task Group 2: IDE Integration Implementation**

Reference: `/root/Zpanel/implementation/phase-1/IDE-integration.md`

#### 2.1 Create IDE Service Layer
**File**: `app/Services/IDEService.php`

Requirements:
- Generate secure tokens for IDE access (32-character random strings)
- Store tokens in cache with 24-hour expiration
- Validate IDE tokens
- Manage user workspace paths (format: `/workspace/user_{userId}/project_{projectId}`)
- Generate authenticated IDE URLs
- Handle workspace isolation per user

Implementation Details:
```php
// Methods required:
- generateToken(int $userId): string
- validateToken(string $token): ?array
- getWorkspacePath(int $userId, ?string $projectId = null): string
- getIDEUrl(string $token, string $workspace): string
```

#### 2.2 Create IDE Controller
**File**: `app/Http/Controllers/IDEController.php`

Requirements:
- Authentication middleware required
- Display IDE interface with iframe
- List user workspaces
- Create new workspaces
- Handle workspace selection
- Pass secure tokens to code-server

Methods:
- `index(Request $request)` - Display IDE with iframe
- `workspaces()` - List user workspaces (API)
- `createWorkspace(Request $request)` - Create workspace (API)

#### 2.3 Create Workspace Model
**File**: `app/Models/Workspace.php`

Fields:
- `user_id` (foreign key to users)
- `name` (string, workspace name)
- `path` (string, filesystem path)
- `type` (string, default: 'default')
- `project_id` (nullable foreign key)
- `settings` (JSON, workspace settings)

Relationships:
- `belongsTo(User::class)`

#### 2.4 Create Database Migration
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_workspaces_table.php`

Schema:
```sql
CREATE TABLE workspaces (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'default',
    project_id BIGINT NULL,
    settings JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 2.5 Create IDE Views
**File**: `resources/views/ide/index.blade.php`

Requirements:
- Extend existing Coolify layout (`@extends('layouts.app')`)
- Full-height iframe for code-server
- Pass IDE URL with authentication token
- Handle clipboard permissions
- Responsive design
- Loading state

#### 2.6 Create IDE Configuration
**File**: `config/ide.php`

Configuration values:
- `code_server_url` (default: 'http://code-server:8080')
- `workspace_base` (default: '/workspace')
- `token_expiry` (default: 24 hours)
- `allowed_extensions` (array of file extensions)
- `default_settings` (editor configuration)

#### 2.7 Add IDE Routes
**File**: `routes/web.php` (append to existing)

Routes:
```php
Route::middleware(['auth'])->prefix('ide')->name('ide.')->group(function () {
    Route::get('/', [IDEController::class, 'index'])->name('index');
    Route::get('/workspaces', [IDEController::class, 'workspaces'])->name('workspaces');
    Route::post('/workspaces', [IDEController::class, 'createWorkspace'])->name('workspaces.create');
});
```

#### 2.8 Update Docker Compose
**File**: `docker-compose.dev.yml` (add service)

Service configuration:
```yaml
code-server:
  image: codercom/code-server:latest
  container_name: zpanel-code-server
  ports:
    - "${CODE_SERVER_PORT:-8080}:8080"
  volumes:
    - ide-workspaces:/workspace
    - code-server-config:/home/coder/.config
  environment:
    - PASSWORD=${CODE_SERVER_PASSWORD:-}
    - PROXY_DOMAIN=${CODE_SERVER_DOMAIN:-}
  networks:
    - coolify-network
  restart: unless-stopped
```

#### 2.9 Update Environment Variables
**File**: `.env` (add these)

Variables:
```env
CODE_SERVER_URL=http://code-server:8080
CODE_SERVER_PORT=8080
CODE_SERVER_PASSWORD=
IDE_WORKSPACE_BASE=/workspace
IDE_TOKEN_EXPIRY=24
```

#### 2.10 Testing IDE Integration
- [ ] Run migrations: `php artisan migrate`
- [ ] Restart Docker services: `docker-compose -f docker-compose.dev.yml up -d`
- [ ] Access `/ide` route while authenticated
- [ ] Verify code-server loads in iframe
- [ ] Test workspace creation
- [ ] Verify token authentication works
- [ ] Test workspace isolation between users

---

### **Task Group 3: MCP Server Framework Enhancement**

Reference: `/root/Zpanel/implementation/phase-1/MCP-server-enhancement.md`

#### 3.1 Analyze Existing MCP Implementation
- [ ] Review existing `.mcp.json` file
- [ ] Find Laravel Boost MCP command: `php artisan boost:mcp`
- [ ] Identify MCP-related files in vendor and app directories
- [ ] Document current MCP capabilities

#### 3.2 Create MCP Server Model
**File**: `app/Models/MCPServer.php`

Requirements:
- Store MCP server configurations in database
- Support multiple server types (cloudflare, github, database, docker, custom)
- Track server status (active, inactive, error)
- JSON configuration storage

Fields:
- `name` (unique string, server identifier)
- `type` (string: cloudflare, github, database, docker, custom)
- `config` (JSON, server-specific configuration)
- `status` (enum: active, inactive, error)
- `last_error` (text, nullable)
- `last_health_check` (timestamp, nullable)

Constants:
```php
const TYPE_CLOUDFLARE = 'cloudflare';
const TYPE_GITHUB = 'github';
const TYPE_DATABASE = 'database';
const TYPE_DOCKER = 'docker';
const TYPE_CUSTOM = 'custom';
const STATUS_ACTIVE = 'active';
const STATUS_INACTIVE = 'inactive';
const STATUS_ERROR = 'error';
```

#### 3.3 Create Database Migration
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_mcp_servers_table.php`

Schema:
```sql
CREATE TABLE mcp_servers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    type VARCHAR(100) NOT NULL,
    config JSON NOT NULL,
    status ENUM('active', 'inactive', 'error') DEFAULT 'active',
    last_error TEXT NULL,
    last_health_check TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (type)
);
```

#### 3.4 Create MCP Server Registry Service
**File**: `app/Services/MCP/ServerRegistry.php`

Requirements:
- Register new MCP servers
- Retrieve MCP servers by name
- List all active servers
- Update server status
- Health check functionality
- Generate `.mcp.json` configuration dynamically

Methods:
- `register(string $name, array $config): MCPServer`
- `getServer(string $name): ?MCPServer`
- `listServers(): array`
- `updateStatus(string $name, string $status): bool`
- `healthCheck(string $name): array`
- `generateConfig(): array`

#### 3.5 Create MCP Server Controller
**File**: `app/Http/Controllers/MCPServerController.php`

Requirements:
- Authentication required
- CRUD operations for MCP servers
- Health check endpoint
- Configuration export endpoint

Methods:
- `index()` - List all servers
- `create()` - Show create form
- `store(Request $request)` - Create server
- `edit(MCPServer $mcpServer)` - Show edit form
- `update(Request $request, MCPServer $mcpServer)` - Update server
- `destroy(MCPServer $mcpServer)` - Delete server
- `healthCheck(MCPServer $mcpServer)` - Check server health
- `config()` - Export .mcp.json configuration

#### 3.6 Create MCP Routes
**File**: `routes/web.php` (append)

Routes:
```php
Route::middleware(['auth'])->prefix('mcp')->name('mcp.')->group(function () {
    Route::get('/', [MCPServerController::class, 'index'])->name('index');
    Route::get('/create', [MCPServerController::class, 'create'])->name('create');
    Route::post('/', [MCPServerController::class, 'store'])->name('store');
    Route::get('/{mcpServer}/edit', [MCPServerController::class, 'edit'])->name('edit');
    Route::put('/{mcpServer}', [MCPServerController::class, 'update'])->name('update');
    Route::delete('/{mcpServer}', [MCPServerController::class, 'destroy'])->name('destroy');
    Route::get('/{mcpServer}/health', [MCPServerController::class, 'healthCheck'])->name('health');
    Route::get('/config', [MCPServerController::class, 'config'])->name('config');
});
```

#### 3.7 Create MCP Views
**Files**: 
- `resources/views/mcp/index.blade.php` - List servers
- `resources/views/mcp/create.blade.php` - Create form
- `resources/views/mcp/edit.blade.php` - Edit form

Requirements:
- Use existing Coolify UI components (Livewire/Alpine.js)
- Display server status with color indicators
- Show server type badges
- Health check button with status display
- JSON configuration editor (use textarea or Monaco editor)

#### 3.8 Testing MCP Framework
- [ ] Run migrations: `php artisan migrate`
- [ ] Create test MCP server via UI
- [ ] Verify server appears in database
- [ ] Test health check endpoint
- [ ] Export `.mcp.json` and verify format
- [ ] Update server configuration
- [ ] Delete server and verify cleanup

---

### **Task Group 4: Cloudflare API Client Implementation**

#### 4.1 Create Cloudflare Configuration
**File**: `config/cloudflare.php`

Configuration:
- `api_token` (from environment)
- `account_id` (from environment)
- `api_endpoint` (default: 'https://api.cloudflare.com/client/v4')
- `timeout` (default: 30 seconds)
- `retry_attempts` (default: 3)
- `retry_delay` (default: 1000ms)

#### 4.2 Create Base Cloudflare Service
**File**: `app/Services/Cloudflare/CloudflareService.php`

Requirements:
- HTTP client wrapper for Cloudflare API
- Authentication with API token
- Error handling and retry logic
- Rate limiting handling
- Request logging

Methods:
- `get(string $endpoint, array $params = []): array`
- `post(string $endpoint, array $data = []): array`
- `put(string $endpoint, array $data = []): array`
- `delete(string $endpoint): array`
- `handleError(Response $response): void`

#### 4.3 Create DNS Service
**File**: `app/Services/Cloudflare/DNSService.php`

Methods:
- `listDNSRecords(string $zoneId, array $filters = []): array`
- `getDNSRecord(string $zoneId, string $recordId): array`
- `createDNSRecord(string $zoneId, array $data): array`
- `updateDNSRecord(string $zoneId, string $recordId, array $data): array`
- `deleteDNSRecord(string $zoneId, string $recordId): bool`

#### 4.4 Create Tunnel Service
**File**: `app/Services/Cloudflare/TunnelService.php`

Methods:
- `listTunnels(string $accountId): array`
- `getTunnel(string $accountId, string $tunnelId): array`
- `createTunnel(string $accountId, string $name, string $secret): array`
- `deleteTunnel(string $accountId, string $tunnelId): bool`
- `getTunnelToken(string $accountId, string $tunnelId): string`

#### 4.5 Create Cloudflare Models

**File**: `app/Models/CloudflareZone.php`
Fields: `zone_id`, `name`, `account_id`, `status`, `settings`

**File**: `app/Models/CloudflareTunnel.php`
Fields: `tunnel_id`, `account_id`, `name`, `secret`, `status`, `connections`

**File**: `app/Models/CloudflareDNSRecord.php`
Fields: `record_id`, `zone_id`, `type`, `name`, `content`, `ttl`, `proxied`, `priority`

#### 4.6 Create Database Migrations

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_cloudflare_zones_table.php`
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_cloudflare_tunnels_table.php`
**File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_cloudflare_dns_records_table.php`

#### 4.7 Testing Cloudflare Client
- [ ] Set Cloudflare API token in `.env`
- [ ] Test DNS record listing
- [ ] Test DNS record creation
- [ ] Test tunnel creation
- [ ] Verify error handling
- [ ] Test rate limiting behavior

---

### **Task Group 5: Cloudflare MCP Server**

#### 5.1 Create Cloudflare MCP Server
**File**: `app/Services/MCP/CloudflareMCPServer.php`

Requirements:
- Implement MCP server protocol
- Expose Cloudflare operations as MCP tools
- Handle requests and responses
- Error handling and validation

Tools to implement:
- `cloudflare_list_zones` - List all zones
- `cloudflare_list_dns_records` - List DNS records for zone
- `cloudflare_create_dns_record` - Create DNS record
- `cloudflare_update_dns_record` - Update DNS record
- `cloudflare_delete_dns_record` - Delete DNS record
- `cloudflare_list_tunnels` - List all tunnels
- `cloudflare_create_tunnel` - Create new tunnel
- `cloudflare_delete_tunnel` - Delete tunnel

#### 5.2 Register Cloudflare MCP Server
- [ ] Add to MCP server registry via database seed
- [ ] Update `.mcp.json` with Cloudflare server
- [ ] Test MCP server integration

---

### **Task Group 6: API Gateway Integration (Kong)**

#### 6.1 Add Kong to Docker Compose
**File**: `docker-compose.dev.yml`

Services to add:
- `kong` (Kong Gateway)
- `kong-database` (PostgreSQL for Kong)
- `kong-migrations` (initial setup)

#### 6.2 Create Kong Service
**File**: `app/Services/APIGateway/KongService.php`

Methods:
- `createService(string $name, string $url): array`
- `getService(string $serviceId): array`
- `updateService(string $serviceId, array $data): array`
- `deleteService(string $serviceId): bool`
- `createRoute(string $serviceId, array $paths): array`
- `addPlugin(string $serviceId, string $plugin, array $config): array`
- `manageRateLimiting(string $serviceId, int $limit): array`

#### 6.3 Create API Service Model
**File**: `app/Models/APIService.php`

Fields: `kong_service_id`, `name`, `url`, `routes`, `plugins`, `status`

#### 6.4 Create API Gateway Controller
**File**: `app/Http/Controllers/APIGatewayController.php`

Methods:
- `index()` - List services
- `create()` - Create service form
- `store(Request $request)` - Store service
- `edit(APIService $apiService)` - Edit form
- `update(Request $request, APIService $apiService)` - Update service
- `destroy(APIService $apiService)` - Delete service

#### 6.5 Create Configuration
**File**: `config/api-gateway.php`

Configuration:
- `kong_admin_url` (default: 'http://kong:8001')
- `kong_proxy_url` (default: 'http://kong:8000')
- `default_rate_limit` (default: 1000)

#### 6.6 Testing API Gateway
- [ ] Start Kong services
- [ ] Create test API service
- [ ] Create route for service
- [ ] Add rate limiting plugin
- [ ] Test API requests through Kong proxy

---

## 🔒 Security Considerations

Throughout implementation, ensure:

1. **Authentication & Authorization**
   - All routes require authentication
   - Token-based access for IDE
   - API tokens for external services

2. **Input Validation**
   - Validate all user inputs
   - Sanitize file paths (prevent directory traversal)
   - Validate JSON configurations

3. **Secrets Management**
   - Never commit API tokens or passwords
   - Use environment variables for sensitive data
   - Encrypt sensitive database fields

4. **Workspace Isolation**
   - Ensure users can only access their workspaces
   - Prevent path traversal attacks
   - Docker volume isolation

5. **Rate Limiting**
   - Implement rate limiting on API endpoints
   - Protect Cloudflare API from abuse
   - Monitor API usage

---

## 📝 Code Quality Standards

Follow these standards throughout implementation:

1. **Laravel Best Practices**
   - Use dependency injection
   - Follow repository pattern where appropriate
   - Use Laravel's built-in features (validation, authorization, etc.)
   - Write descriptive comments

2. **Code Style**
   - Follow PSR-12 coding standard
   - Use Laravel Pint for formatting
   - Descriptive variable and method names
   - Type hints for parameters and return types

3. **Testing**
   - Write unit tests for services
   - Feature tests for controllers
   - Test both success and failure scenarios
   - Aim for ≥70% code coverage

4. **Documentation**
   - PHPDoc comments for classes and methods
   - README for each major feature
   - API documentation in OpenAPI format
   - Inline comments for complex logic

---

## ✅ Verification Checklist

After completing all tasks, verify:

### IDE Integration
- [ ] IDE loads correctly at `/ide` route
- [ ] code-server is accessible and functional
- [ ] Workspace creation works
- [ ] User workspace isolation is enforced
- [ ] Tokens expire correctly (24 hours)
- [ ] Multiple users can use IDE simultaneously

### MCP Server Framework
- [ ] MCP servers can be created via UI
- [ ] Server configurations are stored correctly
- [ ] Health checks work for all server types
- [ ] `.mcp.json` is generated correctly
- [ ] Existing Laravel Boost MCP still works
- [ ] Multiple MCP servers can coexist

### Cloudflare Integration
- [ ] Cloudflare API client connects successfully
- [ ] DNS operations work (list, create, update, delete)
- [ ] Tunnel operations work (list, create, delete)
- [ ] Error handling works correctly
- [ ] Rate limiting is respected
- [ ] Cloudflare MCP server responds to tools

### API Gateway (Kong)
- [ ] Kong services start correctly
- [ ] API services can be created
- [ ] Routes are configured correctly
- [ ] Plugins (rate limiting) work
- [ ] API requests proxy through Kong
- [ ] Kong admin API is accessible

### General
- [ ] All migrations run without errors
- [ ] No breaking changes to existing Coolify functionality
- [ ] Docker services start cleanly
- [ ] Environment variables are documented
- [ ] Code follows Laravel conventions
- [ ] Tests pass (if written)
- [ ] No security vulnerabilities introduced

---

## 📁 Files Summary

### Created Files (Total: ~40 files)

**Services (13):**
- `app/Services/IDEService.php`
- `app/Services/MCP/ServerRegistry.php`
- `app/Services/MCP/CloudflareMCPServer.php`
- `app/Services/Cloudflare/CloudflareService.php`
- `app/Services/Cloudflare/DNSService.php`
- `app/Services/Cloudflare/TunnelService.php`
- `app/Services/APIGateway/KongService.php`

**Controllers (3):**
- `app/Http/Controllers/IDEController.php`
- `app/Http/Controllers/MCPServerController.php`
- `app/Http/Controllers/APIGatewayController.php`

**Models (7):**
- `app/Models/Workspace.php`
- `app/Models/MCPServer.php`
- `app/Models/CloudflareZone.php`
- `app/Models/CloudflareTunnel.php`
- `app/Models/CloudflareDNSRecord.php`
- `app/Models/APIService.php`

**Migrations (7):**
- `database/migrations/YYYY_MM_DD_HHMMSS_create_workspaces_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_mcp_servers_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_cloudflare_zones_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_cloudflare_tunnels_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_cloudflare_dns_records_table.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_api_services_table.php`

**Views (~6-9):**
- `resources/views/ide/index.blade.php`
- `resources/views/mcp/index.blade.php`
- `resources/views/mcp/create.blade.php`
- `resources/views/mcp/edit.blade.php`
- `resources/views/api-gateway/*.blade.php` (multiple)

**Configuration (4):**
- `config/ide.php`
- `config/cloudflare.php`
- `config/api-gateway.php`

**Modified Files:**
- `routes/web.php` (add ~25 new routes)
- `docker-compose.dev.yml` (add code-server, kong, kong-database)
- `.env` (add ~10 new variables)

---

## 🎯 Success Criteria

Phase 1 is complete when:

1. ✅ All 6 task groups are implemented
2. ✅ All files are created and functional
3. ✅ Docker services start without errors
4. ✅ All database migrations run successfully
5. ✅ IDE is accessible and functional
6. ✅ MCP servers can be managed via UI
7. ✅ Cloudflare API operations work
8. ✅ Kong API Gateway is operational
9. ✅ No regression in existing Coolify features
10. ✅ Documentation is updated

---

## 🚀 Execution Strategy

**Recommended approach:**

1. **Start with IDE Integration** (most isolated, easiest to test)
2. **Then MCP Framework** (builds on existing MCP support)
3. **Then Cloudflare Client** (independent of MCP)
4. **Then Cloudflare MCP Server** (connects 2 & 3)
5. **Finally API Gateway** (most complex, benefits from learning)

**For each task group:**
- Read the implementation guide thoroughly
- Create all models and migrations first
- Then services and business logic
- Then controllers
- Finally views and routes
- Test each component before moving on

---

## 📚 Reference Documentation

- **Phase 1 Summary**: `/root/Zpanel/implementation/phase-1/phase-1-summary.md`
- **IDE Integration Guide**: `/root/Zpanel/implementation/phase-1/IDE-integration.md`
- **MCP Enhancement Guide**: `/root/Zpanel/implementation/phase-1/MCP-server-enhancement.md`
- **Technical Roadmap**: `/root/Zpanel/research/technical-integration-roadmap.md`
- **Project README**: `/root/Zpanel/README.md`
- **Laravel Documentation**: https://laravel.com/docs/11.x
- **Coolify Documentation**: https://coolify.io/docs
- **MCP Protocol**: https://modelcontextprotocol.io/

---

## 🤝 Additional Notes

- **Maintain backward compatibility** with existing Coolify functionality
- **Follow existing code patterns** in the Coolify codebase
- **Reuse existing components** where possible (auth, UI components, etc.)
- **Test thoroughly** after each major component
- **Document as you go** - update README and guides
- **Ask questions** if anything is unclear in the implementation guides
- **Commit frequently** with descriptive commit messages

---

