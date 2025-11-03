# Zpanel - Self-Hosted Control Panel Project

[![License: Apache-2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Status: In Development](https://img.shields.io/badge/Status-In%20Development-yellow)](docs/status.md)

## Project Overview

**Zpanel** is a comprehensive self-hostable control panel combining features from 1Panel, cPanel, HestiaCP, Dokploy, and Coolify, with advanced integrations including:
- 🤖 AI Integration & MCP Servers
- 💻 Integrated Development Environment (IDE)
- 🔌 API Management & Gateway
- ☁️ Deep Cloudflare Integration & Automation
- 🔐 Local & Remote Secrets Management
- 🚀 And 30+ additional cutting-edge features

## Base Repository

**Forked From**: Coolify (Apache-2.0 License)
- Original Repository: https://github.com/coollabsio/coolify
- **Our Fork**: https://github.com/freqkflag/Zpanel
- Project Name: **Zpanel**
- Already has MCP integration via Laravel Boost
- Laravel framework for rapid development

## Project Structure

```
self-hosted-control-panel/
├── docs/                    # Documentation
│   ├── architecture/        # Architecture documentation
│   ├── api/                 # API documentation
│   └── guides/              # User and developer guides
├── implementation/          # Implementation tracking
│   ├── phase-1/             # Phase 1 work
│   ├── phase-2/             # Phase 2 work
│   └── status.md            # Implementation status
├── research/                # Research documents
│   ├── repository-verification.md
│   ├── license-compatibility-analysis.md
│   ├── architecture-analysis.md
│   └── technical-integration-roadmap.md
└── README.md               # This file
```

## Implementation Phases

### Phase 1: Foundation & Setup (Months 1-3)
- [x] Repository fork & initial setup ✅
- [x] Project structure created ✅
- [ ] Clone Zpanel repository
- [ ] IDE integration (code-server)
- [ ] MCP server framework enhancement
- [ ] Cloudflare API client implementation
- [ ] Basic API Gateway integration

### Phase 2: Advanced Features (Months 4-6)
- [ ] Additional MCP servers
- [ ] API management dashboard
- [ ] Advanced Cloudflare features
- [ ] Performance optimization

## 🚀 Quick Start

### For Users
See [Installation Guide](docs/guides/installation.md) for production setup.

### For Developers
See [Getting Started Guide](docs/guides/getting-started.md) for development environment setup.

**Quick development setup:**
```bash
git clone https://github.com/freqkflag/Zpanel.git
cd Zpanel/implementation/phase-1/Zpanel
cp .env.development.example .env
docker-compose -f docker-compose.dev.yml up -d
docker-compose exec app php artisan migrate --seed
```

Access at: **http://localhost:8000** (login: `test@example.com` / `password`)

## 📚 Documentation

### User Documentation
- **[Getting Started](docs/guides/getting-started.md)** - Development setup guide
- **[Installation](docs/guides/installation.md)** - Production installation
- **[IDE Integration](docs/guides/ide-integration.md)** - Using the integrated IDE
- **[MCP Integration](docs/guides/mcp-integration.md)** - MCP server usage

### Developer Documentation
- **[API Overview](docs/api/overview.md)** - REST API documentation
- **[API Authentication](docs/api/authentication.md)** - API token management
- **[API Examples](docs/api/examples.md)** - Code samples
- **[System Architecture](docs/architecture/system-overview.md)** - High-level architecture
- **[Deployment Flow](docs/architecture/deployment-flow.md)** - How deployments work
- **[Contributing](implementation/phase-1/Zpanel/CONTRIBUTING.md)** - Contribution guidelines
- **[Cursor Rules](implementation/phase-1/Zpanel/.cursor/rules/README.mdc)** - AI development assistance

### Project Planning
- **[Implementation Status](docs/status.md)** - Current progress
- **[Phase 1 Summary](implementation/phase-1/phase-1-summary.md)** - Foundation work
- **[Phase 2 Planning](implementation/phase-2/README.md)** - Advanced features
- **[Technical Roadmap](research/technical-integration-roadmap.md)** - Detailed implementation plan
- **[Architecture Analysis](research/architecture-analysis.md)** - Technology decisions
- **[License Analysis](research/license-compatibility-analysis.md)** - License compatibility

## License

Apache-2.0 License (inherited from Coolify base repository)

## 🔗 Repository

- **GitHub**: https://github.com/freqkflag/Zpanel
- **Forked From**: https://github.com/coollabsio/coolify
- **Issue Tracker**: https://github.com/freqkflag/Zpanel/issues
- **Discussions**: https://github.com/freqkflag/Zpanel/discussions

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](implementation/phase-1/Zpanel/CONTRIBUTING.md) for details.

- **Code of Conduct**: [CODE_OF_CONDUCT.md](implementation/phase-1/Zpanel/CODE_OF_CONDUCT.md)
- **Security Policy**: [SECURITY.md](implementation/phase-1/Zpanel/SECURITY.md)
- **Development Workflow**: [Development Guide](implementation/phase-1/Zpanel/.cursor/rules/development-workflow.mdc)

## 📊 Project Status

See [Implementation Status](docs/status.md) for detailed progress tracking.

**Current Phase**: Phase 1 - Foundation & Setup (In Progress)  
**Next Milestone**: IDE Integration Complete

## 🙏 Acknowledgments

- Built on top of [Coolify](https://github.com/coollabsio/coolify) - An amazing open-source project
- Inspired by 1Panel, Dokploy, HestiaCP, and cPanel
- Powered by Laravel, Livewire, and the PHP community

