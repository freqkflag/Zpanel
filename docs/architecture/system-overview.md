# System Architecture Overview

Zpanel is built on a modern, server-side first architecture using Laravel 12 as the foundation.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                       User Interface                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │Dashboard │  │   IDE    │  │   MCP    │  │Cloudflare│   │
│  │ (Livewire)│ │(code-srv)│  │ Servers  │  │   UI     │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                  Laravel Application Layer                   │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐            │
│  │ Controllers│  │  Livewire  │  │   Actions  │            │
│  │            │  │ Components │  │            │            │
│  └────────────┘  └────────────┘  └────────────┘            │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Service Layer                            │   │
│  │  - IDEService      - MCPServerRegistry               │   │
│  │  - CloudflareService - ApplicationDeploymentService  │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Queue System (Horizon)                   │   │
│  │  - DeploymentJobs  - ServerCheckJobs - BackupJobs   │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        ▼                 ▼                 ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  PostgreSQL  │  │    Redis     │  │   Docker     │
│  (Database)  │  │(Cache/Queue) │  │  (Containers)│
└──────────────┘  └──────────────┘  └──────────────┘
        │                 │                 │
        └─────────────────┴─────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                  External Services                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │Cloudflare│  │  GitHub  │  │   Kong   │  │   SSH    │   │
│  │   API    │  │   API    │  │  Gateway │  │  Servers │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Key Components

### 1. Backend Layer

#### Laravel Framework
- **Version**: Laravel 12 (PHP 8.4)
- **Purpose**: Core application logic, routing, authentication
- **Key Directories**:
  - `app/Actions/` - Domain-specific business logic
  - `app/Models/` - Eloquent ORM models
  - `app/Services/` - Service classes for external integrations
  - `app/Jobs/` - Background job processing

#### Actions Pattern
Coolify uses the Actions pattern for complex business logic:

```php
// Example: app/Actions/Application/DeployApplication.php
class DeployApplication
{
    public function execute(Application $application): ApplicationDeploymentQueue
    {
        return DB::transaction(function () use ($application) {
            $deployment = $application->deployments()->create([
                'status' => 'queued',
                'commit_sha' => $application->getLatestCommitSha(),
            ]);
            
            DeployApplicationJob::dispatch($deployment);
            
            return $deployment;
        });
    }
}
```

### 2. Frontend Layer

#### Livewire Components
- **Server-side rendering** with reactive components
- **Location**: `app/Livewire/`
- **Views**: `resources/views/livewire/`
- **Real-time updates** via WebSocket

#### Alpine.js
- **Lightweight JavaScript** for client-side interactions
- **Embedded with Livewire** - no separate installation
- **Use cases**: Dropdowns, modals, tooltips, animations

#### Tailwind CSS
- **Version**: 4.1.4
- **Utility-first** styling approach
- **Dark mode** support throughout
- **Custom components** in Blade templates

### 3. Data Layer

#### PostgreSQL (Primary Database)
- **User data** and authentication
- **Application configurations** and metadata
- **Deployment history** and logs
- **Team management** and permissions

#### Redis (Cache & Queues)
- **Session storage**
- **Queue backend** for Laravel Horizon
- **Real-time data** caching
- **WebSocket** session management

### 4. Infrastructure Layer

#### Docker Orchestration
- **Container deployment** for all applications
- **Docker Compose** generation
- **Volume management** for persistent data
- **Network isolation** for security

#### SSH Management
- **Server communication** via SSH
- **Encrypted key storage**
- **Command execution** on remote servers
- **File transfer** capabilities

## Core Models

### Application
- Represents deployed applications
- Git integration (GitHub, GitLab, Bitbucket, Gitea)
- Build configuration and environment variables
- Deployment queue management

### Server
- Remote servers managed by Zpanel
- SSH connection details
- Docker daemon management
- Resource monitoring

### Service
- Docker Compose services
- Multi-container applications
- Service dependencies
- Network configuration

### Team
- Multi-tenant organization structure
- User membership and roles
- Resource isolation
- Permission management

## Request Flow

### Web Request Flow

```
User Browser
    ↓
Nginx/Traefik (Reverse Proxy)
    ↓
Laravel Router (routes/web.php)
    ↓
Middleware (Auth, Team Access, etc.)
    ↓
Livewire Component or Controller
    ↓
Actions/Services (Business Logic)
    ↓
Models (Data Access)
    ↓
Database (PostgreSQL)
```

### API Request Flow

```
API Client
    ↓
Nginx/Traefik (Reverse Proxy)
    ↓
Laravel Router (routes/api.php)
    ↓
Middleware (Sanctum Auth, Rate Limiting)
    ↓
API Controller
    ↓
Form Request (Validation)
    ↓
Actions/Services (Business Logic)
    ↓
API Resource (Response Formatting)
    ↓
JSON Response
```

### Deployment Flow

```
Git Webhook
    ↓
Webhook Controller (routes/webhooks.php)
    ↓
ApplicationDeploymentJob (Queued)
    ↓
Laravel Horizon (Queue Worker)
    ↓
Docker Build & Deploy
    ↓
Health Check
    ↓
Proxy Configuration Update
    ↓
Deployment Complete
```

## Security Architecture

### Authentication
- **Multi-provider OAuth** (GitHub, GitLab, Google, etc.)
- **API tokens** via Laravel Sanctum
- **Session management** with Redis
- **Two-factor authentication** support

### Authorization
- **Team-based access control**
- **Resource-level policies**
- **Enhanced form components** with built-in authorization
- **API ability scoping**

### Data Protection
- **Encrypted environment variables**
- **Secure SSH key storage**
- **Database encryption** for sensitive data
- **HTTPS enforcement**

## Scalability Considerations

### Horizontal Scaling
- **Multi-server deployment**
- **Load balancing** via Traefik
- **Shared session storage** in Redis
- **Queue worker scaling**

### Performance Optimization
- **Database query optimization** (eager loading, indexes)
- **Redis caching** for expensive operations
- **CDN integration** for static assets
- **WebSocket** for real-time updates

## Monitoring & Observability

### Built-in Monitoring
- **Laravel Horizon** - Queue monitoring
- **Laravel Telescope** - Debug/profiling (dev only)
- **Application logs** - Centralized logging
- **Deployment tracking** - Status and history

### External Integrations
- **Sentry** - Error tracking
- **Custom webhooks** - Notification delivery
- **Metrics export** - Prometheus-compatible

## Technology Stack Summary

- **Backend**: Laravel 12.20.0 (PHP 8.4)
- **Frontend**: Livewire 3.6.4 + Alpine.js + Tailwind 4.1.4
- **Database**: PostgreSQL 15 + Redis 7
- **Queue**: Laravel Horizon 5.33.1
- **Testing**: Pest 3.8.2 + Laravel Dusk 8.3.3
- **Containerization**: Docker + Docker Compose
- **Real-time**: Soketi (WebSocket server)

## Integration Points

### Planned Integrations

1. **IDE Integration** (code-server)
   - Web-based development environment
   - Workspace management
   - File editing capabilities

2. **MCP Server Framework**
   - Multiple MCP server support
   - Cloudflare MCP integration
   - Custom MCP servers

3. **API Gateway** (Kong)
   - Rate limiting and throttling
   - API analytics
   - Request transformation

4. **Cloudflare Integration**
   - DNS management
   - Tunnel automation
   - SSL/TLS management
   - DDoS protection

## Additional Resources

- [Application Architecture](../implementation/phase-1/Zpanel/.cursor/rules/application-architecture.mdc)
- [Deployment Architecture](../implementation/phase-1/Zpanel/.cursor/rules/deployment-architecture.mdc)
- [Database Patterns](../implementation/phase-1/Zpanel/.cursor/rules/database-patterns.mdc)
- [Security Patterns](../implementation/phase-1/Zpanel/.cursor/rules/security-patterns.mdc)
- [Technology Stack](../implementation/phase-1/Zpanel/.cursor/rules/technology-stack.mdc)

