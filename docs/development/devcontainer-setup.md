# Development Container Setup

Use VS Code Dev Containers or GitHub Codespaces for a consistent development environment.

## What is a Dev Container?

A development container is a **Docker container configured for development** with:
- All required tools and dependencies pre-installed
- Consistent environment across team members
- VS Code extensions auto-installed
- Port forwarding configured

## Using with VS Code

### Prerequisites

- VS Code installed
- Docker Desktop running
- Dev Containers extension installed

### Setup

1. **Install Dev Containers extension**
   - Open VS Code
   - Install: `ms-vscode-remote.remote-containers`

2. **Open project in container**
   - Open Zpanel folder in VS Code
   - Press `F1` → "Dev Containers: Reopen in Container"
   - Wait for container to build and start

3. **Start developing**
   - Terminal opens inside container
   - Extensions auto-installed
   - Services running (PostgreSQL, Redis, etc.)

## Using with GitHub Codespaces

### Setup

1. **Open in Codespaces**
   - Go to: https://github.com/freqkflag/Zpanel
   - Click "Code" → "Codespaces" → "Create codespace on main"
   
2. **Wait for environment**
   - Codespace builds and starts
   - All dependencies installed automatically
   - Access via browser or VS Code

3. **Start development**
   - Terminal ready
   - Services running
   - Extensions installed

## Container Features

### Pre-installed Tools

- **PHP 8.4** with all required extensions
- **Composer** (PHP package manager)
- **Node.js 20** and npm
- **Git** and GitHub CLI
- **Docker CLI** (for container management)

### Pre-installed VS Code Extensions

**PHP Development:**
- PHP Intelephense
- Laravel Extra Intellisense
- Laravel Artisan
- Laravel Blade
- Laravel Pint

**Frontend Development:**
- ESLint
- Prettier
- Tailwind CSS IntelliSense
- Livewire extensions

### Port Forwarding

Automatically forwards these ports:

| Port | Service | URL |
|------|---------|-----|
| 8000 | Laravel App | http://localhost:8000 |
| 5432 | PostgreSQL | localhost:5432 |
| 6379 | Redis | localhost:6379 |
| 8080 | code-server | http://localhost:8080 |
| 8025 | Mailpit | http://localhost:8025 |

## Customization

### Customize Extensions

Edit `.devcontainer/devcontainer.json`:

```json
{
  "customizations": {
    "vscode": {
      "extensions": [
        "your-extension-id"
      ]
    }
  }
}
```

### Add Environment Variables

```json
{
  "containerEnv": {
    "APP_ENV": "development",
    "APP_DEBUG": "true"
  }
}
```

### Run Commands on Startup

```json
{
  "postCreateCommand": "composer install && npm install",
  "postStartCommand": "php artisan migrate"
}
```

## Common Tasks

### Running Tests

```bash
# Terminal opens inside container
./vendor/bin/pest

# Or use VS Code testing panel
```

### Database Access

```bash
# Connect to PostgreSQL
psql -h postgres -U coolify -d coolify

# Or use VS Code database extension
```

### Debugging

VS Code debugging is pre-configured:

1. Set breakpoint in code
2. Press `F5` to start debugging
3. Make request to trigger breakpoint
4. Use debug console

## Troubleshooting

### Container Won't Start

```bash
# Rebuild container
F1 → "Dev Containers: Rebuild Container"
```

### Ports Not Forwarding

```bash
# Check port forwarding
F1 → "Ports: Focus on Ports View"

# Manually forward port if needed
F1 → "Forward a Port"
```

### Extension Not Working

```bash
# Reinstall extensions
F1 → "Dev Containers: Rebuild Container Without Cache"
```

## Performance Tips

### Use Named Volumes

Improves file system performance:

```json
{
  "mounts": [
    "source=zpanel-node-modules,target=${containerWorkspaceFolder}/node_modules,type=volume"
  ]
}
```

### Increase Resources

Edit Docker Desktop settings:
- **CPUs**: 4+
- **Memory**: 8GB+
- **Swap**: 2GB+

## Advanced Configuration

### Multi-Container Setup

```json
{
  "dockerComposeFile": [
    "../implementation/phase-1/Zpanel/docker-compose.yml",
    "docker-compose.override.yml"
  ],
  "service": "app",
  "runServices": ["app", "postgres", "redis"]
}
```

### Custom Dockerfile

```json
{
  "build": {
    "dockerfile": "Dockerfile",
    "context": ".."
  }
}
```

## Additional Resources

- [VS Code Dev Containers](https://code.visualstudio.com/docs/devcontainers/containers)
- [GitHub Codespaces](https://docs.github.com/en/codespaces)
- [Development Container Spec](https://containers.dev/)

