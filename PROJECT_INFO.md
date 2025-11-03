# Zpanel Project Information

> **Note**: This file provides quick reference information. For complete details, see [README.md](README.md) and [Implementation Status](docs/status.md).

## Project Details

- **Project Name**: Zpanel
- **GitHub Repository**: https://github.com/freqkflag/Zpanel
- **Forked From**: https://github.com/coollabsio/coolify
- **License**: Apache-2.0
- **Base Technology**: Laravel 12 (PHP 8.4) + Docker
- **Current Branch**: main
- **Version**: 0.1.0-alpha (Phase 1 - In Development)

## Quick Start

See [Getting Started Guide](docs/guides/getting-started.md) for complete setup instructions.

### For Developers

```bash
# Clone and setup
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel/implementation/phase-1/Zpanel

# Quick start with Docker
cp .env.development.example .env
docker-compose -f docker-compose.dev.yml up -d
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app php artisan migrate --seed

# Access at http://localhost:8000
# Login: test@example.com / password
```

### For Production

```bash
# Official installation script (when available)
curl -fsSL https://install.zpanel.dev/install.sh | bash
```

## Project Goals

Zpanel aims to be a comprehensive self-hosted control panel with:

- ✅ AI Integration & MCP Servers
- ✅ Integrated Development Environment (IDE)
- ✅ API Management (Kong/Tyk)
- ✅ Deep Cloudflare Integration
- ✅ Local & Remote Secrets Management
- ✅ 30+ additional cutting-edge features

## Current Status

See [Implementation Status](docs/status.md) for detailed tracking.

**Phase 1: Foundation & Setup** (In Progress)
- ✅ Repository forked and named "Zpanel"
- ✅ Project structure created
- ✅ Comprehensive documentation added
- ✅ CI/CD workflows configured
- ✅ Implementation guides prepared
- 🔄 Docker build optimization (in progress)
- ⏳ IDE integration (planned)
- ⏳ MCP enhancement (planned)

**Phase 2: Advanced Features** (Planned - Months 4-6)
- ⏳ Additional MCP servers
- ⏳ API management dashboard
- ⏳ Advanced Cloudflare features

## Documentation

### Quick Links
- **[Getting Started](docs/guides/getting-started.md)** - Setup your dev environment
- **[API Documentation](docs/api/overview.md)** - REST API reference
- **[Architecture](docs/architecture/system-overview.md)** - System design
- **[Testing](docs/development/testing-strategy.md)** - Testing guide
- **[Code Style](docs/development/code-style.md)** - Coding standards

### Implementation Guides
- Setup Guide: [implementation/phase-1/setup-guide.md](implementation/phase-1/setup-guide.md)
- IDE Integration: [implementation/phase-1/IDE-integration.md](implementation/phase-1/IDE-integration.md)
- MCP Enhancement: [implementation/phase-1/MCP-server-enhancement.md](implementation/phase-1/MCP-server-enhancement.md)
- Technical Roadmap: [research/technical-integration-roadmap.md](research/technical-integration-roadmap.md)

### Developer Resources
- AI Development Rules: [Cursor Rules](implementation/phase-1/Zpanel/.cursor/rules/README.mdc)
- Contributing: [CONTRIBUTING.md](implementation/phase-1/Zpanel/CONTRIBUTING.md)
- Changelog: [CHANGELOG-ZPANEL.md](CHANGELOG-ZPANEL.md)

## Links

- **GitHub**: https://github.com/freqkflag/Zpanel
- **Original Repository**: https://github.com/coollabsio/coolify
- **Coolify Website**: https://coolify.io
- **Coolify Documentation**: https://coolify.io/docs

