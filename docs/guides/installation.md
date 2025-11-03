# Installation Guide

Complete guide to installing and configuring Zpanel for development and production environments.

## Installation Methods

### Development Installation

For local development and testing.

#### Method 1: Docker Development Environment (Recommended)

```bash
# Clone repository
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel

# Copy environment configuration
cp .env.development.example .env

# Start development environment
docker-compose -f docker-compose.dev.yml up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Initialize application
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

Access: **http://localhost:8000**

#### Method 2: Native Development (Advanced)

**Prerequisites:**
- PHP 8.4+ with extensions: `pdo`, `pdo_pgsql`, `mbstring`, `xml`, `bcmath`, `curl`
- PostgreSQL 15+
- Redis 7+
- Node.js 18+

**Steps:**
```bash
# Clone repository
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
createdb zpanel_dev
psql zpanel_dev < database/schema.sql  # if available

# Run migrations
php artisan migrate
php artisan db:seed

# Start services
php artisan serve &           # Laravel server
npm run dev &                 # Frontend build
php artisan queue:work &      # Queue worker
php artisan horizon &         # Queue dashboard
```

### Production Installation

For production deployment.

#### Method 1: Official Installation Script

```bash
# Install Zpanel (based on Coolify installation)
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash
```

**This script will:**
- Install Docker and Docker Compose
- Set up required directories
- Configure reverse proxy
- Initialize database
- Start all services

#### Method 2: Manual Production Setup

**Prerequisites:**
- Ubuntu 22.04+ or Debian 11+ (recommended)
- Root or sudo access
- Domain name pointed to server
- Ports 80 and 443 available

**Step-by-step:**

1. **Install Docker**
```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
```

2. **Clone Zpanel**
```bash
git clone https://github.com/freqkflag/Zpanel.git /opt/zpanel
cd /opt/zpanel
```

3. **Configure Environment**
```bash
cp .env.production.example .env
nano .env
```

**Production environment variables:**
```env
APP_NAME=Zpanel
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_DATABASE=zpanel_production

REDIS_HOST=redis

# Set strong random values
APP_KEY=base64:...
DB_PASSWORD=strong-random-password
REDIS_PASSWORD=strong-random-password

# SSL configuration
SSL_MODE=letsencrypt
DOMAIN=your-domain.com
```

4. **Start Production Services**
```bash
docker-compose -f docker-compose.prod.yml up -d
```

5. **Initialize Application**
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan optimize
```

6. **Create Admin User**
```bash
docker-compose exec app php artisan user:create \
  --email=admin@example.com \
  --password=secure-password \
  --name="Admin User"
```

## Post-Installation Setup

### 1. Configure Reverse Proxy

#### Using Traefik (Automatic)

Traefik is configured automatically via Docker labels.

#### Using Nginx (Manual)

```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 2. SSL Certificate Setup

#### Automatic (Let's Encrypt)

```bash
# Configure in .env
SSL_MODE=letsencrypt
DOMAIN=your-domain.com

# Restart services
docker-compose restart
```

#### Manual Certificate

```bash
# Copy certificates
cp fullchain.pem /opt/zpanel/ssl/
cp privkey.pem /opt/zpanel/ssl/

# Configure in .env
SSL_MODE=custom
SSL_CERT_PATH=/ssl/fullchain.pem
SSL_KEY_PATH=/ssl/privkey.pem
```

### 3. Configure Backup

```bash
# Configure S3-compatible storage
S3_ENDPOINT=https://s3.amazonaws.com
S3_BUCKET=zpanel-backups
S3_ACCESS_KEY=your-access-key
S3_SECRET_KEY=your-secret-key

# Enable automatic backups
BACKUP_ENABLED=true
BACKUP_SCHEDULE="0 2 * * *"  # Daily at 2 AM
```

### 4. Configure Notifications

```env
# Email (Resend)
RESEND_API_KEY=your-resend-key

# Discord
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...

# Slack
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
```

## Verification

### Check Installation

```bash
# Check all containers are running
docker-compose ps

# Expected services:
# - app (Laravel)
# - postgres (Database)
# - redis (Cache/Queue)
# - soketi (WebSocket)
# - traefik (Reverse Proxy)
```

### Access Dashboard

1. Open browser to: https://your-domain.com
2. Log in with admin credentials
3. Check dashboard loads correctly
4. Verify no errors in browser console

### Test Deployment

1. Create a test application
2. Connect to Git repository
3. Trigger deployment
4. Monitor deployment logs
5. Verify application is accessible

## Upgrading

### Upgrading Zpanel

```bash
# Pull latest changes
cd /opt/zpanel
git pull origin main

# Update dependencies
docker-compose exec app composer install --no-dev
docker-compose exec app npm install
docker-compose exec app npm run build

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear caches
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan optimize

# Restart services
docker-compose restart
```

### Upgrading from Coolify

If you're migrating from Coolify to Zpanel:

1. **Backup your Coolify data**
```bash
docker exec coolify-db pg_dump -U coolify > backup.sql
```

2. **Export configurations**
```bash
# Export via Coolify UI: Settings → Backup
```

3. **Install Zpanel** (follow production installation)

4. **Import configurations**
```bash
# Import database
docker exec -i zpanel-db psql -U zpanel < backup.sql

# Restore configurations via Zpanel UI
```

## Configuration

### Essential Configuration

#### Database

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=zpanel
DB_USERNAME=zpanel
DB_PASSWORD=secure-random-password
```

#### Cache & Queue

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=secure-random-password
REDIS_PORT=6379
```

#### Email

```env
MAIL_MAILER=resend
RESEND_API_KEY=your-resend-api-key
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Optional Features

#### GitHub Integration

```env
GITHUB_APP_ID=your-app-id
GITHUB_APP_KEY=path/to/private-key.pem
GITHUB_WEBHOOK_SECRET=your-webhook-secret
```

#### Cloudflare Integration

```env
CLOUDFLARE_API_TOKEN=your-cloudflare-token
CLOUDFLARE_ZONE_ID=your-zone-id
```

#### Monitoring (Sentry)

```env
SENTRY_LARAVEL_DSN=your-sentry-dsn
SENTRY_TRACES_SAMPLE_RATE=0.1
```

## Maintenance

### Daily Operations

```bash
# View logs
docker-compose logs -f app

# Check queue status
php artisan horizon:status

# Check failed jobs
php artisan horizon:failed
```

### Backup & Restore

#### Backup Database

```bash
# Manual backup
docker exec zpanel-db pg_dump -U zpanel zpanel > zpanel-backup-$(date +%Y%m%d).sql

# Automated backup (configured in .env)
php artisan backup:run
```

#### Restore Database

```bash
# Stop application
docker-compose stop app

# Restore database
docker exec -i zpanel-db psql -U zpanel zpanel < backup.sql

# Start application
docker-compose start app
```

### Monitoring

```bash
# Monitor resource usage
docker stats

# Monitor application logs
tail -f storage/logs/laravel.log

# Monitor queue workers
php artisan horizon:list
```

## Security Hardening

### 1. Change Default Credentials

```bash
# Create new admin user
php artisan user:create

# Delete test user
php artisan user:delete test@example.com
```

### 2. Configure Firewall

```bash
# Allow SSH, HTTP, HTTPS
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

### 3. Enable HTTPS

- Use Let's Encrypt (automatic)
- Configure SSL_MODE=letsencrypt in .env
- Ensure domain points to server

### 4. Regular Updates

```bash
# Update system packages
apt update && apt upgrade -y

# Update Zpanel (see Upgrading section)
```

## Performance Tuning

### PHP Optimization

```env
# Optimize for production
APP_DEBUG=false
APP_ENV=production

# PHP opcache (docker/production/php.ini)
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

### Database Optimization

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_deployments_app_id ON application_deployment_queues(application_id);
```

### Redis Tuning

```conf
# redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## Next Steps

- [Getting Started Guide](getting-started.md)
- [Configuration Guide](configuration.md)
- [Development Workflow](../../implementation/phase-1/Zpanel/CONTRIBUTING.md)
- [API Documentation](../api/overview.md)

## Support

- **Documentation**: [Zpanel Docs](../README.md)
- **Community**: Discord (link TBD)
- **Issues**: [GitHub Issues](https://github.com/freqkflag/Zpanel/issues)
- **Original Coolify**: [Coolify.io](https://coolify.io)

