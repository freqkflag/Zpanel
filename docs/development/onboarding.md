# Developer Onboarding Guide

Welcome to the Zpanel development team! This guide will help you get started quickly.

## Day 1: Environment Setup

### Morning: Prerequisites

1. **Install required tools** (2-3 hours)
   - [ ] Git
   - [ ] Docker Desktop or Docker Engine
   - [ ] PHP 8.4+ with required extensions
   - [ ] Composer
   - [ ] Node.js 18+ and npm
   - [ ] Code editor (VS Code or Cursor recommended)

2. **Clone repository** (30 minutes)
   ```bash
   git clone https://github.com/freqkflag/Zpanel.git
   cd Zpanel
   ```

3. **Read key documentation** (1 hour)
   - [ ] [README.md](../../README.md)
   - [ ] [CONTRIBUTING.md](../../implementation/phase-1/Zpanel/CONTRIBUTING.md)
   - [ ] [CODE_OF_CONDUCT.md](../../implementation/phase-1/Zpanel/CODE_OF_CONDUCT.md)

### Afternoon: Development Environment

4. **Set up development environment** (2-3 hours)
   - Follow [Getting Started Guide](../guides/getting-started.md)
   - Get environment running
   - Access dashboard at http://localhost:8000
   - Log in with test credentials

5. **Explore the codebase** (1-2 hours)
   - Review directory structure
   - Understand key components
   - Read [System Architecture](../architecture/system-overview.md)

## Day 2: Understanding the Stack

### Morning: Technology Deep Dive

6. **Learn Laravel basics** (if needed) (3-4 hours)
   - [ ] [Laravel Documentation](https://laravel.com/docs)
   - [ ] Routing, Controllers, Models
   - [ ] Middleware, Validation
   - [ ] Eloquent ORM

7. **Understand Livewire** (2 hours)
   - [ ] [Livewire Documentation](https://livewire.laravel.com/docs)
   - [ ] Component structure
   - [ ] Data binding
   - [ ] Events and listeners

### Afternoon: Zpanel Specifics

8. **Review cursor rules** (1 hour)
   - [ ] [Cursor Rules](../../implementation/phase-1/Zpanel/.cursor/rules/README.mdc)
   - [ ] Code conventions
   - [ ] Testing patterns
   - [ ] Security requirements

9. **Understand deployment flow** (2 hours)
   - [ ] [Deployment Flow](../architecture/deployment-flow.md)
   - [ ] How applications are deployed
   - [ ] Docker orchestration
   - [ ] Queue system

## Day 3: Making Your First Contribution

### Morning: Setup Development Workflow

10. **Configure development tools** (1 hour)
    ```bash
    # Install pre-commit hooks
    pip install pre-commit
    pre-commit install
    
    # Configure IDE
    # Follow IDE-specific setup in docs/development/
    ```

11. **Run tests** (1 hour)
    ```bash
    # Run all tests
    cd implementation/phase-1/Zpanel
    ./vendor/bin/pest
    
    # Ensure everything passes
    ```

### Afternoon: First Code Change

12. **Pick a good first issue** (30 minutes)
    - Look for issues labeled: `good first issue`
    - Or: Fix a small documentation issue
    - Or: Add a test for existing code

13. **Create feature branch** (15 minutes)
    ```bash
    git checkout -b feature/your-feature-name
    ```

14. **Make changes** (2-4 hours)
    - Write code following conventions
    - Add tests for new functionality
    - Update documentation if needed

15. **Run quality checks** (30 minutes)
    ```bash
    # Format code
    ./vendor/bin/pint
    
    # Run static analysis
    ./vendor/bin/phpstan analyse
    
    # Run tests
    ./vendor/bin/pest
    ```

16. **Submit pull request** (30 minutes)
    - Commit changes
    - Push to GitHub
    - Create PR using template
    - Request review

## Week 1: Dive Deeper

### Core Components to Understand

- **Models** (`app/Models/`)
  - [ ] Application
  - [ ] Server
  - [ ] Service
  - [ ] Team
  - [ ] User

- **Actions** (`app/Actions/`)
  - [ ] Application deployment
  - [ ] Server management
  - [ ] Database operations

- **Jobs** (`app/Jobs/`)
  - [ ] Background processing
  - [ ] Queue system
  - [ ] Job priorities

- **Livewire Components** (`app/Livewire/`)
  - [ ] Dashboard
  - [ ] Application management
  - [ ] Server monitoring

### Key Concepts

- **Multi-tenancy** - Team-based resource isolation
- **Queue system** - Background job processing with Horizon
- **Real-time updates** - WebSocket integration
- **Docker orchestration** - Container management
- **SSH communication** - Remote server control

## Learning Resources

### Internal Documentation

- [System Architecture](../architecture/system-overview.md)
- [Deployment Flow](../architecture/deployment-flow.md)
- [Database Patterns](../../implementation/phase-1/Zpanel/.cursor/rules/database-patterns.mdc)
- [Testing Strategy](testing-strategy.md)
- [Code Style Guide](code-style.md)

### External Resources

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Livewire 3 Documentation](https://livewire.laravel.com/docs/quickstart)
- [Pest PHP Testing](https://pestphp.com/docs/installation)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Docker Documentation](https://docs.docker.com/)

### Video Tutorials

- [Laracasts - Laravel From Scratch](https://laracasts.com/series/laravel-from-scratch)
- [Livewire Screencasts](https://laravel-livewire.com/screencasts)

## Common Tasks Reference

### Running the Application

```bash
# Development mode
cd implementation/phase-1/Zpanel
docker-compose -f docker-compose.dev.yml up

# Access at: http://localhost:8000
```

### Making Changes

```bash
# Create branch
git checkout -b feature/my-feature

# Make changes...

# Run checks
./vendor/bin/pint
./vendor/bin/pest

# Commit
git add .
git commit -m "feat: description"

# Push
git push origin feature/my-feature
```

### Database Operations

```bash
# Create migration
php artisan make:migration create_workspaces_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh database with seeding
php artisan migrate:fresh --seed
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test tests/Feature/ApplicationTest.php

# Run with coverage
php artisan test --coverage --min=70
```

## Getting Help

### Internal Resources

- **Team chat**: [Discord/Slack channel]
- **Documentation**: `docs/` directory
- **Issue tracker**: GitHub Issues

### Code Review

- PRs require approval from at least 1 maintainer
- Automated checks must pass
- Follow PR template
- Respond to feedback promptly

### Pair Programming

Schedule pair programming sessions:
- Great for complex features
- Knowledge sharing
- Code review in real-time

## Common Pitfalls

### 1. Forgetting to run migrations

```bash
# After pulling changes, always:
php artisan migrate
```

### 2. Not clearing cache

```bash
# Clear all caches after config changes:
php artisan optimize:clear
```

### 3. Docker issues

```bash
# Full reset if things break:
docker-compose down -v
docker-compose up -d
php artisan migrate:fresh --seed
```

### 4. Testing in wrong environment

- **Unit tests**: Run locally
- **Feature tests**: Run in Docker
- See [Testing Strategy](testing-strategy.md)

## Your First Week Checklist

- [ ] Environment setup complete
- [ ] All tests passing locally
- [ ] Read core documentation
- [ ] Understand architecture
- [ ] Made first contribution
- [ ] PR submitted and merged
- [ ] Familiar with development workflow
- [ ] Know where to get help

## Week 2-4 Goals

- [ ] Complete 3-5 PRs
- [ ] Review others' PRs
- [ ] Deep dive into one major component
- [ ] Write/improve documentation
- [ ] Participate in team discussions
- [ ] Understand deployment process

## Month 2-3 Goals

- [ ] Own a feature area
- [ ] Mentor new contributors
- [ ] Contribute to architecture decisions
- [ ] Improve test coverage
- [ ] Optimize performance

## Tips for Success

1. **Ask questions** - No question is too small
2. **Read code** - Best way to learn
3. **Write tests** - Ensure quality
4. **Document** - Help future developers
5. **Communicate** - Keep team informed
6. **Iterate** - Start small, improve incrementally

## Welcome!

We're excited to have you on the team. Don't hesitate to reach out if you need help!

## Additional Resources

- [Development Workflow](../../implementation/phase-1/Zpanel/.cursor/rules/development-workflow.mdc)
- [Contributing Guide](../../implementation/phase-1/Zpanel/CONTRIBUTING.md)
- [Code of Conduct](../../implementation/phase-1/Zpanel/CODE_OF_CONDUCT.md)

