# IDE Integration Guide

Zpanel provides integrated web-based IDE functionality using code-server (VS Code in the browser).

## Overview

The IDE integration allows users to:
- Edit code directly in the browser
- Access project files in isolated workspaces
- Use full VS Code features and extensions
- Collaborate with team members
- Integrate with Git workflows

## Architecture

```
User Browser → Zpanel (Laravel) → code-server (Docker) → Workspace Files
```

**Components:**
- **code-server**: VS Code running in Docker container
- **IDEService**: Laravel service for token generation and workspace management
- **IDEController**: Handles IDE routing and authentication
- **Workspace Model**: Manages user workspaces and settings

## Installation

### Prerequisites

- Zpanel installed and running
- Docker and Docker Compose configured
- Sufficient disk space for workspaces (recommended: 10GB+)

### Step 1: Enable IDE Feature

Add to `.env`:
```env
IDE_ENABLED=true
CODE_SERVER_URL=http://code-server:8080
CODE_SERVER_PORT=8080
CODE_SERVER_PASSWORD=your-secure-password
IDE_WORKSPACE_BASE=/workspace
IDE_TOKEN_EXPIRY=24
```

### Step 2: Start code-server Container

The code-server container is defined in `docker-compose.yml`:

```yaml
services:
  code-server:
    image: codercom/code-server:latest
    container_name: coolify-code-server
    ports:
      - "${CODE_SERVER_PORT:-8080}:8080"
    volumes:
      - ide-workspaces:/workspace
      - code-server-config:/home/coder/.config
    environment:
      - PASSWORD=${CODE_SERVER_PASSWORD}
      - PROXY_DOMAIN=${CODE_SERVER_DOMAIN:-}
    networks:
      - coolify-network
    restart: unless-stopped
```

Start the service:
```bash
docker-compose up -d code-server
```

### Step 3: Verify Installation

```bash
# Check code-server is running
docker ps | grep code-server

# Check logs
docker logs coolify-code-server

# Test direct access (optional)
curl http://localhost:8080
```

## Usage

### Accessing the IDE

1. Log in to Zpanel dashboard
2. Navigate to **Tools** → **IDE** (or `/ide`)
3. Select or create a workspace
4. IDE opens in browser with your workspace loaded

### Creating Workspaces

**Via UI:**
1. Navigate to **IDE** → **Workspaces**
2. Click **Create Workspace**
3. Enter name and select project (optional)
4. Click **Create**

**Via API:**
```bash
curl -X POST https://your-domain.com/ide/workspaces \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "my-project-workspace",
    "project_id": 1
  }'
```

### Workspace Structure

Each workspace is isolated per user:

```
/workspace/
├── user_1/
│   ├── default/           # Default workspace
│   ├── project_1/         # Project-specific workspace
│   └── project_2/
└── user_2/
    ├── default/
    └── custom_workspace/
```

## Features

### File Management

- **Create/Edit/Delete** files and folders
- **Upload files** via drag-and-drop
- **Download files** to local machine
- **Search** across files
- **Git integration** (commit, push, pull)

### Code Editing

- **Syntax highlighting** for 100+ languages
- **IntelliSense** and auto-completion
- **Code navigation** (go to definition, find references)
- **Multi-cursor** editing
- **Snippets** and templates

### Terminal

- **Integrated terminal** with full shell access
- **Multiple terminals** support
- **Custom shells** (bash, zsh, fish)
- **Workspace-scoped** terminal sessions

### Extensions

Install VS Code extensions:
1. Open **Extensions** panel (Ctrl+Shift+X)
2. Search for extension
3. Click **Install**

**Recommended extensions:**
- PHP Intelephense
- Laravel Extension Pack
- Tailwind CSS IntelliSense
- Docker
- GitLens

### Git Integration

- **Source control** panel
- **Commit** changes
- **Push/Pull** to remote
- **Branch** management
- **Diff** viewer

## Configuration

### IDE Settings

Configure in `config/ide.php`:

```php
return [
    'code_server_url' => env('CODE_SERVER_URL', 'http://code-server:8080'),
    'workspace_base' => env('IDE_WORKSPACE_BASE', '/workspace'),
    'token_expiry' => env('IDE_TOKEN_EXPIRY', 24), // hours
    
    'allowed_extensions' => [
        'php', 'js', 'ts', 'vue', 'css', 'html', 'json',
        'py', 'java', 'go', 'rust', 'cpp', 'c', 'sql'
    ],
    
    'default_settings' => [
        'editor.fontSize' => 14,
        'editor.fontFamily' => 'Consolas, "Courier New", monospace',
        'editor.wordWrap' => 'on',
        'editor.minimap.enabled' => true,
        'workbench.colorTheme' => 'Default Dark+',
    ],
];
```

### User Settings

Users can customize their IDE experience:

1. Open **Settings** (Ctrl+,)
2. Modify preferences
3. Settings persist across sessions

### Workspace Settings

Per-workspace configuration:

```json
// .vscode/settings.json in workspace
{
  "editor.formatOnSave": true,
  "php.suggest.basic": false,
  "intelephense.files.maxSize": 5000000
}
```

## Security

### Authentication

- **Token-based** authentication
- **24-hour** token expiry (configurable)
- **Automatic logout** on token expiration
- **Session sharing** with Zpanel

### Workspace Isolation

- **Per-user** workspaces
- **Path validation** to prevent directory traversal
- **Team-based** access control
- **File permissions** enforcement

### Best Practices

- ✅ Use strong code-server password
- ✅ Enable HTTPS for IDE access
- ✅ Regular token rotation
- ✅ Limit workspace storage quotas
- ❌ Don't share IDE tokens
- ❌ Don't store sensitive data in workspaces

## Troubleshooting

### IDE Won't Load

**Symptoms**: Blank screen or loading error

**Solutions:**
```bash
# Check code-server is running
docker ps | grep code-server

# Restart code-server
docker-compose restart code-server

# Check logs
docker logs coolify-code-server
```

### Connection Timeout

**Symptoms**: "Connection timeout" or "Cannot connect"

**Solutions:**
```bash
# Check network connectivity
docker network inspect coolify-network

# Verify code-server health
curl http://localhost:8080/healthz

# Check firewall rules
ufw status
```

### File Permission Errors

**Symptoms**: "Permission denied" when editing files

**Solutions:**
```bash
# Fix workspace permissions
docker exec coolify-code-server chown -R coder:coder /workspace

# Verify user permissions
docker exec coolify-code-server ls -la /workspace
```

### Extensions Won't Install

**Symptoms**: Extension installation fails

**Solutions:**
```bash
# Check internet connectivity in container
docker exec coolify-code-server curl -I https://marketplace.visualstudio.com

# Increase timeout
# Add to code-server config:
export VSCODE_MARKETPLACE_TIMEOUT=30000
```

## Advanced Configuration

### Custom Extensions

Pre-install extensions for all users:

```bash
# In code-server container
docker exec coolify-code-server \
  code-server --install-extension ms-python.python
```

### Workspace Templates

Create workspace templates:

```bash
# Create template directory
mkdir -p /workspace/templates/laravel

# Add starter files
cp -r /path/to/laravel-starter/* /workspace/templates/laravel/
```

### Resource Limits

Configure in `docker-compose.yml`:

```yaml
code-server:
  deploy:
    resources:
      limits:
        cpus: '2.0'
        memory: 4G
      reservations:
        cpus: '1.0'
        memory: 2G
```

## Performance Optimization

### Enable Extension Host

```env
# Improve extension performance
VSCODE_EXTENSION_HOST_COUNT=3
```

### Configure Cache

```bash
# Increase code-server cache
docker exec coolify-code-server mkdir -p /home/coder/.cache
```

### Optimize Workspace Indexing

```json
// In workspace settings.json
{
  "files.watcherExclude": {
    "**/node_modules/**": true,
    "**/vendor/**": true,
    "**/storage/**": true
  }
}
```

## Integration with Zpanel Features

### Git Integration

- **Auto-sync** with connected repositories
- **Webhook triggers** for automatic updates
- **Branch switching** from IDE
- **Deployment triggers** from IDE

### Terminal Integration

Access Zpanel's terminal features directly:

```bash
# Deploy from IDE terminal
php artisan app:deploy my-application

# Check server status
php artisan server:status
```

## Future Enhancements (Planned)

- [ ] Collaborative editing (multiple users)
- [ ] Live Share integration
- [ ] Code review tools
- [ ] Integrated debugging
- [ ] Container shell access
- [ ] File synchronization
- [ ] Workspace snapshots

## Additional Resources

- [code-server Documentation](https://coder.com/docs/code-server)
- [VS Code Documentation](https://code.visualstudio.com/docs)
- [IDE Implementation Guide](../../implementation/phase-1/IDE-integration.md)
- [Zpanel API](../api/overview.md)

## Support

Issues specific to IDE integration:
- [GitHub Issues](https://github.com/freqkflag/Zpanel/issues)
- Tag with: `component:ide`

