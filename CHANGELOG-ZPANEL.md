# Zpanel Changelog

All notable changes specific to the Zpanel project will be documented in this file.

For changes related to the base Coolify functionality, see [Coolify Changelog](implementation/phase-1/Zpanel/CHANGELOG.md).

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Project Setup
- Forked from Coolify (https://github.com/coollabsio/coolify)
- Created Zpanel repository at https://github.com/freqkflag/Zpanel
- Established project structure and documentation framework

### Documentation
- Created comprehensive documentation structure
  - API documentation (overview, authentication, examples)
  - Architecture documentation (system overview, deployment flow)
  - User guides (getting started, installation, IDE, MCP)
  - Implementation tracking (Phase 1, Phase 2 planning)
- Added `docs/status.md` for implementation tracking
- Created `CHANGELOG-ZPANEL.md` for project-specific changes
- Enhanced README with badges and better structure

### Research & Planning
- Completed repository verification analysis
- Completed license compatibility analysis (chose Apache-2.0)
- Completed architecture analysis (Laravel + Coolify base)
- Created technical integration roadmap
- Defined Phase 1 and Phase 2 implementation plans

### Development Setup
- Configured development environment based on Coolify
- Set up project structure for Zpanel-specific features
- Created implementation guides:
  - IDE integration guide
  - MCP server enhancement guide
  - Setup guide

## Phase 1 Development (In Progress)

### Foundation & Setup
- [ ] Repository fork and initial setup ✅
- [ ] Project structure created ✅
- [ ] IDE integration (code-server) ⏳
- [ ] MCP server framework enhancement ⏳
- [ ] Cloudflare API client implementation ⏳
- [ ] Basic API Gateway integration ⏳

### Current Work
- Docker build caching optimization (Task #00001)
- Docker cleanup scheduling fixes (Task #00002)
- Resource operations UI simplification (Task #00003)

## Phase 2 Development (Planned)

### Advanced Features (Months 4-6)
- [ ] Additional MCP servers (GitHub, Database, Docker)
- [ ] API management dashboard
- [ ] Advanced Cloudflare features
- [ ] Performance optimization

## Version History

### [0.1.0-alpha] - 2025-11-03

Initial alpha release with project foundation.

#### Added
- Project documentation structure
- Development environment setup
- Implementation roadmaps
- Research and analysis documents
- Comprehensive cursor rules for AI assistance
- Security policy and contributing guidelines

#### Inherited from Coolify v4.0.0-beta.437
- Laravel 12 application framework
- Livewire 3.5+ frontend components
- Docker-based deployment system
- Multi-server management
- Team-based multi-tenancy
- OAuth integration
- API with OpenAPI documentation
- Queue system with Laravel Horizon
- Real-time WebSocket support

## Future Versions

### [0.2.0] - Planned
- IDE integration complete
- Enhanced MCP framework
- Cloudflare client implemented

### [0.3.0] - Planned
- API Gateway integration
- Additional MCP servers
- Advanced Cloudflare features

### [1.0.0] - Target
- Production-ready release
- All Phase 1 & 2 features complete
- Comprehensive documentation
- Full test coverage
- Security audit complete

## Contributing

See [CONTRIBUTING.md](implementation/phase-1/Zpanel/CONTRIBUTING.md) for contribution guidelines.

## License

Apache-2.0 - See [LICENSE](implementation/phase-1/Zpanel/LICENSE) for details.

Forked from Coolify which is also Apache-2.0 licensed.

