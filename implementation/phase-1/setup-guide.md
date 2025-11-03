# Phase 1 Setup Guide

## Prerequisites

- Git installed
- Docker and Docker Compose installed
- PHP 8.1+ and Composer
- Node.js and npm (for frontend assets)
- Code editor (VS Code recommended)

## Project Information

- **Project Name**: Zpanel
- **Repository**: https://github.com/freqkflag/Zpanel
- **Forked From**: https://github.com/coollabsio/coolify
- **License**: Apache-2.0

## Step 1: Clone Zpanel Repository

```bash
cd /root/self-hosted-control-panel/implementation/phase-1
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel
```

## Step 2: Review Existing MCP Integration

```bash
# Check MCP configuration
cat .mcp.json

# Look for Laravel Boost MCP implementation
find . -name "*mcp*" -o -name "*boost*" | grep -i mcp
```

## Step 3: Set Up Development Environment

```bash
# Install PHP dependencies
composer install

# Install Node dependencies (if any)
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set up Docker environment
docker-compose up -d
```

## Step 4: Verify Setup

```bash
# Check Docker services
docker-compose ps

# Run Laravel migrations
php artisan migrate

# Test Laravel installation
php artisan serve
# Visit http://localhost:8000
```

## Step 5: Create Development Branch

```bash
git checkout -b feature/phase-1-integrations
git push -u origin feature/phase-1-integrations
```

## Step 6: Review Current Architecture

Document the following:
- Application structure (`app/` directory)
- Routes (`routes/web.php`, `routes/api.php`)
- Controllers (`app/Http/Controllers/`)
- Services (`app/Services/`)
- Models (`app/Models/`)
- Database structure (`database/migrations/`)
- Docker setup (`docker-compose.yml`)

## Next Steps

After setup is complete, proceed to:
1. IDE Integration implementation
2. MCP Server Framework enhancement
3. Cloudflare API client implementation

See individual task files for detailed implementation steps.

