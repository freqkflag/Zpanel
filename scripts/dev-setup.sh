#!/bin/bash

# Zpanel Development Setup Script
# This script automates the development environment setup

set -e  # Exit on error

echo "🚀 Zpanel Development Setup"
echo "=============================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running from correct directory
if [ ! -f "README.md" ]; then
    echo -e "${RED}Error: Please run this script from the Zpanel root directory${NC}"
    exit 1
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
echo "📋 Checking prerequisites..."

if ! command_exists docker; then
    echo -e "${RED}✗ Docker not found${NC}"
    echo "  Install: https://docs.docker.com/get-docker/"
    exit 1
fi
echo -e "${GREEN}✓ Docker found${NC}"

if ! command_exists docker-compose; then
    echo -e "${RED}✗ Docker Compose not found${NC}"
    echo "  Install: https://docs.docker.com/compose/install/"
    exit 1
fi
echo -e "${GREEN}✓ Docker Compose found${NC}"

if ! command_exists php; then
    echo -e "${YELLOW}⚠ PHP not found (optional for native development)${NC}"
else
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    echo -e "${GREEN}✓ PHP $PHP_VERSION found${NC}"
fi

if ! command_exists node; then
    echo -e "${YELLOW}⚠ Node.js not found (optional for native development)${NC}"
else
    NODE_VERSION=$(node -v)
    echo -e "${GREEN}✓ Node.js $NODE_VERSION found${NC}"
fi

echo ""

# Navigate to Zpanel directory
cd implementation/phase-1/Zpanel || exit 1

# Setup environment file
echo "📝 Setting up environment file..."
if [ ! -f .env ]; then
    if [ -f .env.development.example ]; then
        cp .env.development.example .env
        echo -e "${GREEN}✓ Created .env from .env.development.example${NC}"
    elif [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Created .env from .env.example${NC}"
    else
        echo -e "${RED}✗ No .env.example file found${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠ .env already exists, skipping${NC}"
fi

echo ""

# Start Docker services
echo "🐳 Starting Docker services..."
docker-compose -f docker-compose.dev.yml up -d

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 5

# Check if services are running
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}✓ Docker services started${NC}"
else
    echo -e "${RED}✗ Docker services failed to start${NC}"
    docker-compose logs
    exit 1
fi

echo ""

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if docker-compose exec -T app composer install; then
    echo -e "${GREEN}✓ PHP dependencies installed${NC}"
else
    echo -e "${RED}✗ Failed to install PHP dependencies${NC}"
    exit 1
fi

echo ""

# Install Node dependencies
echo "📦 Installing Node.js dependencies..."
if docker-compose exec -T app npm install; then
    echo -e "${GREEN}✓ Node.js dependencies installed${NC}"
else
    echo -e "${RED}✗ Failed to install Node.js dependencies${NC}"
    exit 1
fi

echo ""

# Generate app key
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate --force

echo ""

# Run migrations
echo "🗄️  Running database migrations..."
if docker-compose exec -T app php artisan migrate --force; then
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Migrations failed (this may be normal on first run)${NC}"
fi

echo ""

# Seed database
echo "🌱 Seeding database..."
if docker-compose exec -T app php artisan db:seed --force; then
    echo -e "${GREEN}✓ Database seeded${NC}"
else
    echo -e "${YELLOW}⚠ Seeding failed (this may be normal on first run)${NC}"
fi

echo ""

# Setup complete
echo "=============================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=============================="
echo ""
echo "🌐 Access your application:"
echo "   Dashboard: http://localhost:8000"
echo "   Login: test@example.com"
echo "   Password: password"
echo ""
echo "🔧 Development tools:"
echo "   Horizon: http://localhost:8000/horizon"
echo "   Mailpit: http://localhost:8025"
echo ""
echo "📝 Next steps:"
echo "   1. Read the Getting Started guide: docs/guides/getting-started.md"
echo "   2. Review the architecture: docs/architecture/system-overview.md"
echo "   3. Check the development workflow: docs/development/onboarding.md"
echo ""
echo "🛠️  Useful commands:"
echo "   View logs: docker-compose logs -f app"
echo "   Run tests: docker-compose exec app ./vendor/bin/pest"
echo "   Stop services: docker-compose down"
echo ""
echo "Happy coding! 🚀"

