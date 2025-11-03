#!/bin/bash
set -euo pipefail

###########################################
# Zpanel Production Deployment Script
# Self-hosted control panel deployment
###########################################

echo "🚀 Zpanel Production Deployment"
echo "================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
ZPANEL_VERSION="${ZPANEL_VERSION:-latest}"
DEPLOYMENT_DIR="${DEPLOYMENT_DIR:-$(pwd)}"
ENV_FILE="${ENV_FILE:-.env}"

echo -e "${BLUE}ℹ Deployment Configuration:${NC}"
echo "  Version: $ZPANEL_VERSION"
echo "  Directory: $DEPLOYMENT_DIR"
echo "  Environment: $ENV_FILE"
echo ""

# Check prerequisites
echo -e "${BLUE}📋 Checking prerequisites...${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓${NC} Docker installed"

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}✗ Docker Compose is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓${NC} Docker Compose installed"

# Check environment file
if [ ! -f "$ENV_FILE" ]; then
    echo -e "${YELLOW}⚠ Environment file not found. Creating from example...${NC}"
    if [ -f ".env.example" ]; then
        cp .env.example "$ENV_FILE"
        echo -e "${GREEN}✓${NC} Created $ENV_FILE from .env.example"
        echo -e "${YELLOW}⚠ Please edit $ENV_FILE with your configuration before continuing${NC}"
        echo ""
        echo "Required variables:"
        echo "  - APP_NAME"
        echo "  - APP_URL"
        echo "  - DB_PASSWORD"
        echo "  - APP_KEY (will be generated)"
        echo ""
        read -p "Press Enter after configuring $ENV_FILE..."
    else
        echo -e "${RED}✗ No .env.example file found${NC}"
        exit 1
    fi
fi

# Generate APP_KEY if not set
if ! grep -q "^APP_KEY=.\\+" "$ENV_FILE"; then
    echo -e "${BLUE}🔑 Generating application key...${NC}"
    APP_KEY=$(openssl rand -base64 32)
    sed -i "s/^APP_KEY=.*/APP_KEY=base64:$APP_KEY/" "$ENV_FILE"
    echo -e "${GREEN}✓${NC} Application key generated"
fi

# Pull latest images
echo ""
echo -e "${BLUE}📦 Pulling Docker images...${NC}"
docker-compose -f docker-compose.prod.yml pull

# Build custom images if needed
echo -e "${BLUE}🔨 Building Zpanel images...${NC}"
docker-compose -f docker-compose.prod.yml build

# Stop existing containers
if docker-compose -f docker-compose.prod.yml ps -q 2>/dev/null | grep -q .; then
    echo -e "${BLUE}🛑 Stopping existing containers...${NC}"
    docker-compose -f docker-compose.prod.yml down
    echo -e "${GREEN}✓${NC} Containers stopped"
fi

# Start services
echo ""
echo -e "${BLUE}🚀 Starting Zpanel services...${NC}"
docker-compose -f docker-compose.prod.yml up -d

# Wait for database
echo -e "${BLUE}⏳ Waiting for database...${NC}"
sleep 5

# Run migrations
echo -e "${BLUE}📊 Running database migrations...${NC}"
docker-compose -f docker-compose.prod.yml exec -T zpanel php artisan migrate --force

# Clear caches
echo -e "${BLUE}🧹 Clearing caches...${NC}"
docker-compose -f docker-compose.prod.yml exec -T zpanel php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T zpanel php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T zpanel php artisan view:cache

# Check service health
echo ""
echo -e "${BLUE}🏥 Checking service health...${NC}"
sleep 3

if docker-compose -f docker-compose.prod.yml ps | grep -q "Up"; then
    echo -e "${GREEN}✓${NC} Services are running"
else
    echo -e "${RED}✗${NC} Some services failed to start"
    docker-compose -f docker-compose.prod.yml ps
    exit 1
fi

# Display service URLs
echo ""
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}   Zpanel Deployed Successfully! 🎉   ${NC}"
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo ""
echo "📍 Access your Zpanel installation at:"
echo "   → $(grep ^APP_URL= "$ENV_FILE" | cut -d'=' -f2)"
echo ""
echo "📊 Monitoring:"
echo "   → Horizon: $(grep ^APP_URL= "$ENV_FILE" | cut -d'=' -f2)/horizon"
echo "   → Health: $(grep ^APP_URL= "$ENV_FILE" | cut -d'=' -f2)/api/health"
echo ""
echo "🔧 Management Commands:"
echo "   View logs:    docker-compose -f docker-compose.prod.yml logs -f zpanel"
echo "   Restart:      docker-compose -f docker-compose.prod.yml restart"
echo "   Stop:         docker-compose -f docker-compose.prod.yml down"
echo "   Shell access: docker-compose -f docker-compose.prod.yml exec zpanel bash"
echo ""
echo "📚 Documentation: https://github.com/freqkflag/Zpanel"
echo ""

