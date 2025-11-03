# Getting Started with Zpanel

Welcome to Zpanel! This guide will help you get your development environment set up and running.

## Prerequisites

Before you begin, ensure you have the following installed:

- **Git**: Version control system
- **Docker**: Container runtime (v20.10+)
- **Docker Compose**: Container orchestration (v2.0+)
- **PHP**: 8.4 or higher
- **Composer**: PHP dependency manager
- **Node.js**: 18+ and npm
- **Code Editor**: VS Code, Cursor, or similar

### System Requirements

- **OS**: Linux (Ubuntu 22.04+ recommended), macOS, or Windows with WSL2
- **RAM**: Minimum 2GB, recommended 4GB+
- **Storage**: 20GB+ available space
- **Network**: Stable internet connection

## Quick Start

### 1. Clone the Repository

```bash
# Clone Zpanel
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel

# Or if you're starting from the meta-project
cd /root/Zpanel/implementation/phase-1/Zpanel
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment example
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file with your settings
nano .env
```

**Key environment variables:**
```env
APP_NAME=Zpanel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=coolify
DB_USERNAME=coolify
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Start Docker Services

```bash
# Start all services (PostgreSQL, Redis, Soketi, etc.)
docker-compose -f docker-compose.dev.yml up -d

# Check services are running
docker-compose ps
```

Expected output:
```
NAME                    STATUS              PORTS
coolify-db              Up                  5432/tcp
coolify-redis           Up                  6379/tcp
coolify-realtime        Up                  6001/tcp
```

### 5. Initialize Database

```bash
# Run migrations
php artisan migrate

# Seed development data
php artisan db:seed
```

### 6. Start Development Server

#### Option A: Using PHP Built-in Server

```bash
# Start Laravel server
php artisan serve

# In another terminal, start frontend build
npm run dev

# In another terminal, start queue worker
php artisan queue:work
```

#### Option B: Using Spin (Recommended)

```bash
# Install Spin
# See: https://serversideup.net/open-source/spin/docs/installation

# Start all services
spin up
```

Access your application at: **http://localhost:8000**

### 7. Default Login Credentials

```
Email: test@example.com
Password: password
```

## Development Tools

### Laravel Horizon (Queue Monitoring)

Access at: **http://localhost:8000/horizon**

Monitor background jobs, failed jobs, and queue metrics.

### Laravel Telescope (Debugging)

Enable in `.env`:
```env
TELESCOPE_ENABLED=true
```

Access at: **http://localhost:8000/telescope**

### Mailpit (Email Testing)

Access at: **http://localhost:8025**

All emails sent during development are caught here.

## Verify Installation

### Run Tests

```bash
# Run unit tests
./vendor/bin/pest tests/Unit

# Run feature tests (in Docker)
docker exec coolify php artisan test
```

### Check Code Quality

```bash
# Format code
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# All checks
./vendor/bin/pint && ./vendor/bin/pest
```

### Access Dashboard

1. Open browser to http://localhost:8000
2. Log in with test credentials
3. Explore the dashboard
4. Check server status
5. Review deployment logs

## Project Structure

```
Zpanel/
├── app/                    # Laravel application
│   ├── Actions/           # Business logic actions
│   ├── Http/              # Controllers, middleware
│   ├── Jobs/              # Background jobs
│   ├── Livewire/          # Frontend components
│   ├── Models/            # Eloquent models
│   └── Services/          # Service classes
├── config/                # Configuration files
├── database/              # Migrations, seeders
├── docs/                  # Documentation (you are here)
├── public/                # Public assets
├── resources/             # Views, CSS, JS
├── routes/                # Route definitions
├── tests/                 # Test suites
└── docker-compose.yml     # Docker configuration
```

## Common Development Tasks

### Creating a Migration

```bash
php artisan make:migration create_workspaces_table
php artisan migrate
```

### Creating a Model

```bash
php artisan make:model Workspace -mfs
# -m: migration, -f: factory, -s: seeder
```

### Creating a Livewire Component

```bash
php artisan make:livewire Application/Show
```

### Running Specific Tests

```bash
# Run tests in a specific file
php artisan test tests/Feature/ApplicationTest.php

# Run tests matching a pattern
php artisan test --filter=deployment
```

## Troubleshooting

### Port Already in Use

```bash
# Find process using port 8000
lsof -i :8000

# Kill the process
kill -9 <PID>
```

### Database Connection Failed

```bash
# Check PostgreSQL is running
docker-compose ps postgres

# Restart database
docker-compose restart postgres

# Check logs
docker-compose logs postgres
```

### Permission Errors

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache
```

### Docker Issues

```bash
# Restart all containers
docker-compose down
docker-compose up -d

# Remove all containers and volumes (clean slate)
docker-compose down -v
docker-compose up -d
php artisan migrate:fresh --seed
```

## Next Steps

After completing the setup:

1. **Explore the codebase**: Read [Architecture Overview](../architecture/system-overview.md)
2. **Learn the development workflow**: Read [Development Workflow](../../implementation/phase-1/Zpanel/CONTRIBUTING.md)
3. **Understand testing**: Read [Testing Patterns](../../implementation/phase-1/Zpanel/.cursor/rules/testing-patterns.mdc)
4. **Review security**: Read [Security Patterns](../../implementation/phase-1/Zpanel/.cursor/rules/security-patterns.mdc)
5. **Start contributing**: Pick an issue or feature to implement

## Additional Resources

- [Contributing Guide](../../implementation/phase-1/Zpanel/CONTRIBUTING.md)
- [API Documentation](../api/overview.md)
- [Technology Stack](../../implementation/phase-1/Zpanel/TECH_STACK.md)
- [Cursor Rules](.../../implementation/phase-1/Zpanel/.cursor/rules/README.mdc)
- [Original Coolify Docs](https://coolify.io/docs)

## Getting Help

- **Discord**: Join the Coolify community
- **GitHub Issues**: Report bugs or request features
- **Discussions**: Ask questions and share ideas

Happy coding! 🚀

