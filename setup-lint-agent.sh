#!/bin/bash

# Coolify Lint Agent Setup Script
# This script sets up the automatic linting agent for development

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}════════════════════════════════════════${NC}"
echo -e "${BLUE}  Coolify Lint Agent Setup${NC}"
echo -e "${BLUE}════════════════════════════════════════${NC}"
echo ""

# Check if running from correct directory
if [ ! -f "lint-agent.js" ]; then
    echo -e "${RED}❌ Error: Must run from /root/Zpanel directory${NC}"
    echo -e "${YELLOW}💡 cd /root/Zpanel && ./setup-lint-agent.sh${NC}"
    exit 1
fi

PROJECT_ROOT="/root/Zpanel/implementation/phase-1/Zpanel"

# Check prerequisites
echo -e "${BLUE}📋 Checking prerequisites...${NC}"

command -v node >/dev/null 2>&1 || { echo -e "${RED}❌ Node.js is required but not installed${NC}"; exit 1; }
command -v npm >/dev/null 2>&1 || { echo -e "${RED}❌ npm is required but not installed${NC}"; exit 1; }
command -v php >/dev/null 2>&1 || { echo -e "${RED}❌ PHP is required but not installed${NC}"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo -e "${RED}❌ Composer is required but not installed${NC}"; exit 1; }

echo -e "${GREEN}✅ All prerequisites found${NC}"
echo ""

# Install Node.js dependencies for lint agent
echo -e "${BLUE}📦 Installing lint agent dependencies...${NC}"
npm install

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Lint agent dependencies installed${NC}"
else
    echo -e "${RED}❌ Failed to install lint agent dependencies${NC}"
    exit 1
fi
echo ""

# Check Laravel project
if [ ! -d "$PROJECT_ROOT" ]; then
    echo -e "${YELLOW}⚠️  Warning: Laravel project not found at ${PROJECT_ROOT}${NC}"
    echo -e "${YELLOW}   Skipping Laravel dependencies installation${NC}"
else
    echo -e "${BLUE}📦 Installing Laravel project dependencies...${NC}"
    
    # Install Composer dependencies
    if [ -f "$PROJECT_ROOT/composer.json" ]; then
        echo -e "${BLUE}   Installing PHP dependencies...${NC}"
        cd "$PROJECT_ROOT"
        composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1 | grep -v "Warning:"
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ PHP dependencies installed${NC}"
        else
            echo -e "${YELLOW}⚠️  Some PHP dependencies may have issues${NC}"
        fi
    fi
    
    # Install npm dependencies
    if [ -f "$PROJECT_ROOT/package.json" ]; then
        echo -e "${BLUE}   Installing JavaScript dependencies...${NC}"
        npm install --prefix "$PROJECT_ROOT"
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ JavaScript dependencies installed${NC}"
        else
            echo -e "${YELLOW}⚠️  Some JavaScript dependencies may have issues${NC}"
        fi
    fi
    
    cd /root/Zpanel
fi
echo ""

# Make lint-agent.js executable
echo -e "${BLUE}🔧 Making lint agent executable...${NC}"
chmod +x lint-agent.js
echo -e "${GREEN}✅ Lint agent is now executable${NC}"
echo ""

# Check if Pint exists
if [ -f "$PROJECT_ROOT/vendor/bin/pint" ]; then
    echo -e "${GREEN}✅ Laravel Pint found${NC}"
else
    echo -e "${YELLOW}⚠️  Laravel Pint not found. PHP linting will not work until you run:${NC}"
    echo -e "${YELLOW}   cd $PROJECT_ROOT && composer install${NC}"
fi
echo ""

# Offer to set up as systemd service
echo -e "${BLUE}🚀 Setup Options:${NC}"
echo ""
echo -e "1. ${GREEN}Run manually${NC} (for development):"
echo -e "   ${YELLOW}npm run lint:watch${NC}"
echo ""
echo -e "2. ${GREEN}Install as systemd service${NC} (runs in background):"
echo -e "   ${YELLOW}sudo cp lint-agent.service /etc/systemd/system/${NC}"
echo -e "   ${YELLOW}sudo systemctl daemon-reload${NC}"
echo -e "   ${YELLOW}sudo systemctl enable lint-agent@\$USER${NC}"
echo -e "   ${YELLOW}sudo systemctl start lint-agent@\$USER${NC}"
echo ""
echo -e "${GREEN}✅ Setup complete!${NC}"
echo ""
echo -e "${BLUE}📖 For more information, see: README-LINT-AGENT.md${NC}"
echo ""

# Offer to start immediately
read -p "$(echo -e ${YELLOW}Would you like to start the lint agent now? [y/N]: ${NC})" -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo -e "${GREEN}🚀 Starting lint agent...${NC}"
    echo -e "${YELLOW}Press Ctrl+C to stop${NC}"
    echo ""
    sleep 1
    node lint-agent.js
fi

