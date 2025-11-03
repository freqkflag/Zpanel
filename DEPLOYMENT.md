# Zpanel Production Deployment Guide

**Version**: 1.0.0  
**Last Updated**: November 3, 2025

---

## 🚀 Quick Start (5 Minutes)

### Prerequisites
- Docker 20.10+ installed
- Docker Compose 2.0+ installed
- 2GB+ RAM available
- 20GB+ disk space

### One-Command Deployment

```bash
curl -fsSL https://raw.githubusercontent.com/freqkflag/Zpanel/main/implementation/phase-1/Zpanel/deploy.sh | bash
```

Or manual deployment:

```bash
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel/implementation/phase-1/Zpanel
cp .env.production.example .env
# Edit .env with your settings
./deploy.sh
```

---

## 📋 Detailed Installation

### Step 1: Clone Repository

```bash
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel/implementation/phase-1/Zpanel
```

### Step 2: Configure Environment

```bash
cp .env.production.example .env
```

Edit `.env` and set these required variables:

```env
APP_URL=https://your-domain.com
DB_PASSWORD=your_secure_database_password
CODE_SERVER_PASSWORD=your_secure_ide_password
KONG_DB_PASSWORD=your_secure_kong_password
```

### Step 3: Generate Application Key

```bash
# This is done automatically by deploy.sh, or manually:
openssl rand -base64 32
# Add to .env as: APP_KEY=base64:generated_key_here
```

### Step 4: Deploy

```bash
chmod +x deploy.sh
./deploy.sh
```

The script will:
- ✅ Check prerequisites
- ✅ Pull Docker images
- ✅ Start all services
- ✅ Run database migrations
- ✅ Configure caching
- ✅ Verify health

---

## 🏗️ Architecture

### Services Deployed

| Service | Container | Port | Purpose |
|---------|-----------|------|---------|
| **Zpanel** | zpanel-app | 80 | Main application |
| **PostgreSQL** | zpanel-postgres | 5432 | Database |
| **Redis** | zpanel-redis | 6379 | Cache & Queues |
| **Soketi** | zpanel-soketi | 6001 | WebSocket |
| **code-server** | zpanel-code-server | 8080 | IDE |
| **Kong** | zpanel-kong | 8000/8001 | API Gateway |
| **Kong DB** | zpanel-kong-db | - | Kong Database |

### Docker Volumes

- `zpanel_storage` - Application storage
- `zpanel_backups` - Database backups
- `zpanel_postgres_data` - PostgreSQL data
- `zpanel_redis_data` - Redis persistence
- `zpanel_ide_workspaces` - IDE workspaces
- `zpanel_ide_config` - IDE configuration
- `zpanel_kong_postgres` - Kong database

---

## 🔧 Configuration

### Required Environment Variables

```env
# Application (Required)
APP_NAME=Zpanel
APP_KEY=base64:your_key_here
APP_URL=https://your-domain.com
DB_PASSWORD=secure_password

# IDE (Required)
CODE_SERVER_PASSWORD=ide_password

# Kong (Required)
KONG_DB_PASSWORD=kong_password
```

### Optional Integrations

```env
# Cloudflare
CLOUDFLARE_API_TOKEN=your_cloudflare_token
CLOUDFLARE_ACCOUNT_ID=your_account_id

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

---

## 🏥 Health Checks

### Service Health Endpoints

```bash
# Main application
curl http://localhost/api/health

# Kong Gateway
curl http://localhost:8001/status

# Database
docker-compose -f docker-compose.prod.yml exec postgres pg_isready

# Redis
docker-compose -f docker-compose.prod.yml exec redis redis-cli ping
```

### Monitoring

```bash
# View all service status
docker-compose -f docker-compose.prod.yml ps

# Check logs
docker-compose -f docker-compose.prod.yml logs -f zpanel

# Resource usage
docker stats
```

---

## 🔐 Security

### Firewall Configuration

```bash
# Allow HTTP/HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Allow SSH (if needed)
ufw allow 22/tcp

# Enable firewall
ufw enable
```

### SSL/TLS Setup

1. **Using Cloudflare** (Recommended):
   - Point domain to your server
   - Enable Cloudflare proxy
   - Use Flexible or Full SSL

2. **Using Let's Encrypt**:
   ```bash
   # Install certbot
   apt install certbot
   
   # Get certificate
   certbot certonly --standalone -d your-domain.com
   
   # Update .env
   APP_URL=https://your-domain.com
   ```

### Access Control

- Change default passwords in `.env`
- Use strong DATABASE passwords
- Enable 2FA in Zpanel settings
- Restrict SSH access
- Use API tokens for automation

---

## 📊 Maintenance

### Backup

```bash
# Backup database
docker-compose -f docker-compose.prod.yml exec postgres pg_dump -U zpanel zpanel > backup.sql

# Backup volumes
docker run --rm \
  -v zpanel_storage:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/zpanel-storage.tar.gz /data

# Backup environment
cp .env .env.backup
```

### Updates

```bash
# Pull latest version
git pull origin main

# Rebuild and restart
./deploy.sh
```

### Database Migrations

```bash
# Run migrations
docker-compose -f docker-compose.prod.yml exec zpanel php artisan migrate

# Rollback last migration
docker-compose -f docker-compose.prod.yml exec zpanel php artisan migrate:rollback
```

---

## 🐛 Troubleshooting

### Common Issues

**Services won't start:**
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs

# Restart services
docker-compose -f docker-compose.prod.yml restart
```

**Database connection errors:**
```bash
# Verify database is running
docker-compose -f docker-compose.prod.yml ps postgres

# Check credentials
docker-compose -f docker-compose.prod.yml exec zpanel php artisan tinker
>>> DB::connection()->getPdo();
```

**Cache issues:**
```bash
# Clear all caches
docker-compose -f docker-compose.prod.yml exec zpanel php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec zpanel php artisan config:clear
docker-compose -f docker-compose.prod.yml exec zpanel php artisan route:clear
docker-compose -f docker-compose.prod.yml exec zpanel php artisan view:clear
```

**Permission errors:**
```bash
# Fix storage permissions
docker-compose -f docker-compose.prod.yml exec zpanel chown -R www-data:www-data storage bootstrap/cache
```

---

## 📈 Performance Optimization

### Production Optimizations (Auto-applied)

- ✅ OPcache enabled
- ✅ Route caching
- ✅ Config caching
- ✅ View caching
- ✅ Redis for sessions/cache
- ✅ Queue workers via Horizon

### Scaling

**Horizontal Scaling:**
```yaml
# Add more Zpanel containers
services:
  zpanel:
    deploy:
      replicas: 3
```

**Database Optimization:**
```bash
# Increase shared_buffers for PostgreSQL
# Add to docker-compose.prod.yml:
postgres:
  command: postgres -c shared_buffers=256MB -c max_connections=200
```

---

## 🔗 Access Points

After deployment, access:

- **Main App**: `http://your-server/` or configured `APP_URL`
- **IDE**: Access via Zpanel UI → IDE menu
- **Horizon**: `http://your-server/horizon`
- **API Docs**: `http://your-server/docs/api`
- **Kong Admin**: Internal only (port 8001)
- **Health Check**: `http://your-server/api/health`

---

## 📞 Support

- **GitHub**: https://github.com/freqkflag/Zpanel
- **Issues**: https://github.com/freqkflag/Zpanel/issues
- **Documentation**: `/docs` in repository
- **Based on**: [Coolify](https://github.com/coollabsio/coolify) (Apache-2.0)

---

**Deployment Guide Version**: 1.0.0  
**Zpanel Version**: Phase 1 Complete  
**License**: Apache-2.0

