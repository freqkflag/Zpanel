# Docker Orchestration Architecture

Detailed documentation of how Zpanel orchestrates Docker containers for application deployments.

## Overview

Zpanel uses **Docker and Docker Compose** to containerize and deploy applications across multiple servers.

## Docker Architecture

```
┌─────────────────────────────────────────────────────┐
│              Zpanel Control Plane                    │
│  ┌─────────────────────────────────────────────┐   │
│  │    Laravel Application (app container)      │   │
│  │    - Receives deployment requests           │   │
│  │    - Generates configurations               │   │
│  │    - Queues deployment jobs                 │   │
│  └─────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
                       │
                       │ SSH Connection
                       ▼
┌─────────────────────────────────────────────────────┐
│              Target Server (Remote)                  │
│  ┌─────────────────────────────────────────────┐   │
│  │    Docker Engine                             │   │
│  │    ┌──────────┐  ┌──────────┐  ┌─────────┐ │   │
│  │    │  App 1   │  │  App 2   │  │  DB     │ │   │
│  │    │Container │  │Container │  │Container│ │   │
│  │    └──────────┘  └──────────┘  └─────────┘ │   │
│  └─────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────┐   │
│  │    Traefik (Reverse Proxy)                   │   │
│  │    - Routes traffic to containers            │   │
│  │    - SSL termination                         │   │
│  │    - Load balancing                          │   │
│  └─────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────┘
```

## Container Types

### 1. Zpanel System Containers

Core containers running on Zpanel control server:

- **coolify** - Main Laravel application
- **coolify-db** - PostgreSQL database
- **coolify-redis** - Redis cache/queue
- **coolify-realtime** - Soketi WebSocket server
- **coolify-proxy** - Traefik reverse proxy
- **code-server** - IDE container (planned)

### 2. User Application Containers

Containers deployed on target servers:

- **Application containers** - User applications
- **Database containers** - PostgreSQL, MySQL, MongoDB, etc.
- **Service containers** - Redis, Elasticsearch, etc.
- **Custom containers** - User-defined services

## Docker Compose Generation

### Configuration Generation Process

1. **Read application settings** (git repo, environment vars, etc.)
2. **Generate docker-compose.yml** dynamically
3. **Add Traefik labels** for routing
4. **Configure volumes** for persistence
5. **Set up networks** for isolation
6. **Write compose file** to server

### Generated Compose Example

```yaml
version: '3.8'

services:
  app:
    image: ${IMAGE_NAME}:${TAG}
    container_name: ${CONTAINER_NAME}
    restart: unless-stopped
    
    environment:
      - NODE_ENV=production
      - DATABASE_URL=${DATABASE_URL}
      # ... more env vars
    
    labels:
      - traefik.enable=true
      - traefik.http.routers.${APP_NAME}.rule=Host(`${FQDN}`)
      - traefik.http.routers.${APP_NAME}.entrypoints=websecure
      - traefik.http.routers.${APP_NAME}.tls=true
      - traefik.http.routers.${APP_NAME}.tls.certresolver=letsencrypt
      - traefik.http.services.${APP_NAME}.loadbalancer.server.port=${PORT}
    
    volumes:
      - app-storage:/app/storage
      - app-uploads:/app/public/uploads
    
    networks:
      - coolify
    
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '0.5'
          memory: 512M

networks:
  coolify:
    external: true

volumes:
  app-storage:
    driver: local
  app-uploads:
    driver: local
```

## Image Building

### Build Strategies

#### 1. Dockerfile-based

User provides Dockerfile in repository:

```dockerfile
FROM node:18-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --production
COPY . .
RUN npm run build

FROM node:18-alpine
WORKDIR /app
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
CMD ["node", "dist/server.js"]
```

#### 2. Nixpacks Auto-detection

Zpanel detects framework and generates optimized Dockerfile:

```bash
# Zpanel runs:
nixpacks build /path/to/repo --name my-app
```

Supports: Node.js, Python, Go, Rust, PHP, Ruby, Java, .NET, etc.

#### 3. Buildpacks

Heroku-compatible buildpacks:

```bash
# Zpanel runs:
pack build my-app --builder heroku/buildpacks:20
```

### Build Caching

**BuildKit Cache Mounts:**
```dockerfile
# Cache npm dependencies
RUN --mount=type=cache,target=/root/.npm \
    npm ci --production

# Cache apt packages
RUN --mount=type=cache,target=/var/cache/apt \
    apt-get update && apt-get install -y package
```

**Registry Cache:**
```bash
docker buildx build \
  --cache-from type=registry,ref=myregistry/myapp:cache \
  --cache-to type=registry,ref=myregistry/myapp:cache,mode=max \
  -t myregistry/myapp:latest .
```

## Volume Management

### Volume Types

1. **Named volumes** - Docker-managed storage
2. **Bind mounts** - Host directory mounts
3. **tmpfs mounts** - Temporary memory storage

### Volume Strategies

**Persistent Data:**
```yaml
volumes:
  - app-data:/app/data         # Application data
  - app-uploads:/app/uploads   # User uploads
  - app-logs:/app/logs         # Log files
```

**Configuration:**
```yaml
volumes:
  - ./config:/app/config:ro    # Read-only config
```

**Temporary:**
```yaml
tmpfs:
  - /tmp
  - /var/tmp
```

### Backup Integration

Volumes are backed up to S3-compatible storage:

```php
// app/Jobs/BackupVolume.php
class BackupVolume implements ShouldQueue
{
    public function handle(): void
    {
        $volumeName = $this->volume->name;
        $backupPath = "/tmp/backup-{$volumeName}.tar.gz";
        
        // Create volume backup
        Process::run("docker run --rm -v {$volumeName}:/data -v /tmp:/backup alpine tar czf /backup/backup-{$volumeName}.tar.gz -C /data .");
        
        // Upload to S3
        Storage::disk('s3')->put("backups/{$volumeName}.tar.gz", file_get_contents($backupPath));
        
        // Cleanup
        unlink($backupPath);
    }
}
```

## Network Configuration

### Network Types

1. **Bridge networks** - Default container networking
2. **Overlay networks** - Multi-host networking (Docker Swarm)
3. **Host networks** - Direct host network access
4. **None** - No networking

### Zpanel Network Strategy

**Default bridge network:**
```yaml
networks:
  coolify:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16
```

**Application isolation:**
- Each application gets its own network
- Databases on shared network if needed
- Explicit service linking

**Example:**
```yaml
networks:
  app-network:
    driver: bridge
  database-network:
    driver: bridge

services:
  app:
    networks:
      - app-network
      - database-network
  
  database:
    networks:
      - database-network
```

## Container Lifecycle

### 1. Creation

```bash
docker-compose up -d
```

**Process:**
1. Pull/build image
2. Create container
3. Configure networking
4. Mount volumes
5. Start container
6. Wait for health check

### 2. Updates

```bash
# Zero-downtime update
docker-compose up -d --no-deps --build app
```

**Process:**
1. Build new image
2. Start new container
3. Wait for health check
4. Update proxy config
5. Stop old container
6. Remove old container

### 3. Deletion

```bash
docker-compose down
```

**Process:**
1. Stop containers
2. Remove containers
3. Optionally remove volumes
4. Remove networks

## Health Checks

### Container Health Checks

```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost:3000/health"]
  interval: 30s
  timeout: 10s
  retries: 3
  start_period: 60s
```

### Health Check Types

**HTTP Check:**
```dockerfile
HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -f http://localhost/ || exit 1
```

**Command Check:**
```dockerfile
HEALTHCHECK CMD node healthcheck.js || exit 1
```

**Script Check:**
```bash
#!/bin/sh
# healthcheck.sh
pg_isready -U postgres || exit 1
```

## Resource Management

### Resource Limits

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '0.5'
          memory: 512M
```

### Monitoring

```bash
# Real-time resource usage
docker stats

# Container logs
docker logs -f container-name

# Inspect container
docker inspect container-name
```

## Security Configuration

### Container Security

```yaml
services:
  app:
    user: "1000:1000"              # Non-root user
    read_only: true                # Read-only filesystem
    security_opt:
      - no-new-privileges:true     # Prevent privilege escalation
      - apparmor:docker-default    # AppArmor profile
    cap_drop:
      - ALL                        # Drop all capabilities
    cap_add:
      - CHOWN                      # Add only needed capabilities
      - SETUID
      - SETGID
```

### Network Security

```yaml
networks:
  internal:
    driver: bridge
    internal: true  # No external access
```

## Traefik Integration

### Automatic Routing

Traefik discovers services via Docker labels:

```yaml
labels:
  - traefik.enable=true
  - traefik.http.routers.myapp.rule=Host(`app.example.com`)
  - traefik.http.services.myapp.loadbalancer.server.port=3000
```

### SSL/TLS

```yaml
labels:
  - traefik.http.routers.myapp.tls=true
  - traefik.http.routers.myapp.tls.certresolver=letsencrypt
```

### Middleware

```yaml
labels:
  - traefik.http.routers.myapp.middlewares=compress,ratelimit
  - traefik.http.middlewares.compress.compress=true
  - traefik.http.middlewares.ratelimit.ratelimit.average=100
```

## Troubleshooting

### Common Issues

**Container won't start:**
```bash
# Check logs
docker logs container-name

# Inspect container
docker inspect container-name

# Check health
docker ps --filter "name=container-name"
```

**Port conflicts:**
```bash
# Find process using port
lsof -i :8080

# Change port in compose file
```

**Network issues:**
```bash
# Inspect network
docker network inspect coolify

# Restart networking
docker network rm coolify
docker network create coolify
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [Traefik Documentation](https://doc.traefik.io/traefik/)
- [Deployment Flow](deployment-flow.md)

