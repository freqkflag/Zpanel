# Zpanel Autonomous Agents

**Version**: 1.0.0  
**System**: MCP Supervisor  
**Location**: `/root/Zpanel/agents/mcp-supervisor`  
**API Endpoint**: `http://localhost:3001` (vps.freqkflag.co)

## 📋 Overview

Zpanel includes a complete Model Context Protocol (MCP) Supervisor system that manages autonomous agent workers. These agents perform specialized tasks in a secure, sandboxed environment with strict safety controls.

### Key Features

- ✅ **9 Production-Ready Agents** - Covering monitoring, data processing, file operations, and more
- 🔒 **Security First** - Sandboxed execution with manifest-based restrictions
- 🚀 **RESTful HTTP API** - Easy integration via HTTP endpoints
- 📊 **Resource Monitoring** - CPU, memory, and runtime tracking
- 🔄 **Auto-Retry** - Automatic retry mechanism for failed tasks
- 🐳 **Docker Support** - Optional containerized execution
- 📝 **Comprehensive Logging** - All operations logged to `logs/actions.log`

### System Architecture

```
┌─────────────────────────────────────┐
│   HTTP API (Express on :3001)       │
│   - 10+ RESTful endpoints           │
│   - Agent lifecycle management      │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│   Agent Monitor                      │
│   - Fork-based execution             │
│   - Resource monitoring              │
│   - Timeout enforcement              │
│   - Auto-retry mechanism             │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│   9 Autonomous Agent Workers         │
│   (See agent catalog below)          │
└──────────────────────────────────────┘
```

---

## 🤖 Agent Catalog

### 1. Example Task Agent

**ID**: `example-task`  
**Category**: Demo  
**Status**: ✅ Enabled  
**Requires Autonomy**: No

**Description**: A simple demo agent that showcases the basic IPC protocol. Use this as a template for creating new agents.

**Capabilities**:
- `demo` - Demonstration purposes
- `template` - Template for new agents

**Default Timeout**: 60 seconds

**Use Cases**:
- Learning the agent system
- Template for creating custom agents
- Testing the supervisor system

---

### 2. Health Checker Agent

**ID**: `health-checker`  
**Category**: Monitoring  
**Status**: ✅ Enabled  
**Requires Autonomy**: No

**Description**: Monitors system health including CPU, memory, disk usage, and process status. Essential for system monitoring and alerting.

**Capabilities**:
- `system-monitoring` - Overall system monitoring
- `cpu-check` - CPU usage monitoring
- `memory-check` - Memory usage monitoring
- `disk-check` - Disk space monitoring
- `process-check` - Process status verification

**Default Timeout**: 60 seconds

**Parameters**:
- `checks` (array, optional): Array of checks to perform: `all`, `cpu`, `memory`, `disk`, `system`, `process`
- `processName` (string, optional): Process name to check (required if checks includes `process`)

**Examples**:
```bash
# Run all health checks
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "health-checker",
    "params": {
      "checks": ["all"]
    }
  }'

# Check only CPU and memory
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "health-checker",
    "params": {
      "checks": ["cpu", "memory"]
    }
  }'

# Check if Node.js is running
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "health-checker",
    "params": {
      "checks": ["process"],
      "processName": "node"
    }
  }'
```

---

### 3. Log Analyzer Agent

**ID**: `log-analyzer`  
**Category**: Monitoring  
**Status**: ✅ Enabled  
**Requires Autonomy**: No

**Description**: Parses and analyzes log files to extract errors, warnings, statistics, and patterns. Useful for troubleshooting and monitoring.

**Capabilities**:
- `log-parsing` - Parse log file formats
- `error-detection` - Extract errors from logs
- `statistics` - Generate log statistics
- `pattern-matching` - Search for patterns

**Default Timeout**: 90 seconds

**Parameters**:
- `logPath` (string, **required**): Path to the log file to analyze
- `operation` (string, optional): Analysis operation: `analyze`, `errors`, `warnings`, `stats`, `search` (default: `analyze`)
- `filters` (object, optional): Optional filters with properties:
  - `since` (string): Filter logs since this date (ISO format)
  - `keyword` (string): Filter by keyword
  - `pattern` (string): Search pattern (regex)

**Examples**:
```bash
# Analyze all logs
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "log-analyzer",
    "params": {
      "logPath": "/root/Zpanel/agents/mcp-supervisor/logs/actions.log",
      "operation": "analyze"
    }
  }'

# Extract only errors
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "log-analyzer",
    "params": {
      "logPath": "/root/Zpanel/agents/mcp-supervisor/logs/actions.log",
      "operation": "errors"
    }
  }'

# Search for specific pattern
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "log-analyzer",
    "params": {
      "logPath": "/root/Zpanel/agents/mcp-supervisor/logs/actions.log",
      "operation": "search",
      "filters": {
        "pattern": "timeout"
      }
    }
  }'
```

---

### 4. API Caller Agent

**ID**: `api-caller`  
**Category**: Network  
**Status**: ✅ Enabled  
**Requires Autonomy**: Yes

**Description**: Makes HTTP/HTTPS requests to external APIs. Supports GET, POST, PUT, DELETE methods with custom headers and request body.

**Capabilities**:
- `http-requests` - Make HTTP/HTTPS requests
- `api-integration` - Integrate with external APIs
- `rest-api` - RESTful API interactions

**Default Timeout**: 60 seconds

**Use Cases**:
- Calling external APIs
- Webhook integrations
- Data fetching from remote services
- API testing and monitoring

---

### 5. File Processor Agent

**ID**: `file-processor`  
**Category**: Filesystem  
**Status**: ✅ Enabled  
**Requires Autonomy**: Yes

**Description**: Performs file operations including copy, move, rename, and organize files by extension, date, or size.

**Capabilities**:
- `file-operations` - General file operations
- `copy` - Copy files and directories
- `move` - Move files and directories
- `rename` - Rename files
- `organize` - Organize files by criteria

**Default Timeout**: 120 seconds

**Use Cases**:
- Batch file operations
- File organization and cleanup
- Automated file management
- Directory synchronization

---

### 6. Data Transformer Agent

**ID**: `data-transformer`  
**Category**: Data  
**Status**: ✅ Enabled  
**Requires Autonomy**: Yes

**Description**: Converts data between different formats (JSON, CSV, XML). Useful for data migration and integration tasks.

**Capabilities**:
- `data-conversion` - Convert between data formats
- `json` - JSON processing
- `csv` - CSV processing
- `xml` - XML processing
- `format-transformation` - Transform data structures

**Default Timeout**: 90 seconds

**Parameters**:
- `inputPath` (string, **required**): Path to the input file
- `outputPath` (string, **required**): Path to save the converted file
- `inputFormat` (string, **required**): Input data format: `json`, `csv`, `xml`
- `outputFormat` (string, **required**): Output data format: `json`, `csv`, `xml`

**Examples**:
```bash
# Convert JSON to CSV
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "data-transformer",
    "params": {
      "inputPath": "/root/Zpanel/agents/mcp-supervisor/workspace/data.json",
      "outputPath": "/root/Zpanel/agents/mcp-supervisor/workspace/data.csv",
      "inputFormat": "json",
      "outputFormat": "csv"
    }
  }'

# Convert CSV to XML
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "data-transformer",
    "params": {
      "inputPath": "/root/Zpanel/agents/mcp-supervisor/workspace/data.csv",
      "outputPath": "/root/Zpanel/agents/mcp-supervisor/workspace/data.xml",
      "inputFormat": "csv",
      "outputFormat": "xml"
    }
  }'
```

---

### 7. Backup Manager Agent

**ID**: `backup-manager`  
**Category**: Filesystem  
**Status**: ✅ Enabled  
**Requires Autonomy**: Yes

**Description**: Creates, restores, and manages file/directory backups with optional compression support.

**Capabilities**:
- `backup` - Create backups
- `restore` - Restore from backups
- `compression` - Compress backup files
- `file-management` - Manage backup files

**Default Timeout**: 180 seconds

**Parameters**:
- `operation` (string, **required**): Operation: `create`, `restore`, or `list`
- `sourcePath` (string, optional): Path to backup (for create operation)
- `backupDir` (string, optional): Directory to store/list backups
- `backupPath` (string, optional): Path to backup file (for restore operation)
- `restorePath` (string, optional): Path to restore backup to
- `compress` (boolean, optional): Compress backup (default: true)

**Examples**:
```bash
# Create compressed backup
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "backup-manager",
    "params": {
      "operation": "create",
      "sourcePath": "/root/Zpanel/agents/mcp-supervisor/workspace",
      "backupDir": "/root/Zpanel/agents/mcp-supervisor/backups",
      "compress": true
    }
  }'

# List all backups
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "backup-manager",
    "params": {
      "operation": "list",
      "backupDir": "/root/Zpanel/agents/mcp-supervisor/backups"
    }
  }'
```

---

### 8. Image Optimizer Agent

**ID**: `image-optimize`  
**Category**: Media  
**Status**: ✅ Enabled  
**Requires Autonomy**: Yes

**Description**: Compresses and optimizes images (JPEG, PNG, WebP) while maintaining quality. Reduces file sizes for faster loading times.

**Capabilities**:
- `image-processing` - Process and manipulate images
- `compression` - Compress images
- `format-conversion` - Convert between image formats

**Default Timeout**: 120 seconds

**Use Cases**:
- Website image optimization
- Batch image compression
- Format conversion (JPEG ↔ PNG ↔ WebP)
- Reducing storage and bandwidth costs

---

### 9. Rebrand Agent

**ID**: `rebrand-agent`  
**Category**: Filesystem  
**Status**: ✅ Enabled  
**Requires Autonomy**: Yes

**Description**: Systematically rebrand content while preserving attributions and licenses. Performs text replacement across multiple files.

**Capabilities**:
- `file-processing` - Process multiple files
- `text-replacement` - Replace text patterns
- `rebranding` - Rebrand projects
- `batch-operations` - Batch file operations

**Default Timeout**: 120 seconds

**Parameters**:
- `targetDir` (string, **required**): Directory to process for rebranding
- `filePattern` (string, optional): Regex pattern for files to process (e.g., `\\.blade\\.php$`)
- `dryRun` (boolean, optional): If true, only report changes without modifying files (default: false)

**Use Cases**:
- Project rebranding
- Systematic text replacement
- Code migration
- Documentation updates

---

## 📊 Agent Categories

### Demo
Agents for demonstration and template purposes.
- `example-task`

### Monitoring
System monitoring, health checks, and log analysis.
- `health-checker`
- `log-analyzer`

### Filesystem
File and directory operations.
- `file-processor`
- `backup-manager`
- `rebrand-agent`

### Network
Network operations and API integrations.
- `api-caller`

### Data
Data transformation and processing.
- `data-transformer`

### Media
Image and video processing.
- `image-optimize`

---

## 🔌 API Reference

### Base URL
```
http://localhost:3001
```

### Run Agent

**Endpoint**: `POST /run-agent`

**Request Body**:
```json
{
  "agentId": "agent-id",
  "params": {
    // Agent-specific parameters
  }
}
```

**Response**:
```json
{
  "runId": "uuid",
  "agentId": "agent-id",
  "status": "running",
  "startedAt": "2025-11-03T12:00:00.000Z"
}
```

### Get Agent Status

**Endpoint**: `GET /status/:runId`

**Response**:
```json
{
  "runId": "uuid",
  "agentId": "agent-id",
  "status": "complete",
  "progress": 100,
  "result": {
    // Agent-specific results
  },
  "startedAt": "2025-11-03T12:00:00.000Z",
  "finishedAt": "2025-11-03T12:00:05.000Z",
  "duration": 5000
}
```

### List All Agents

**Endpoint**: `GET /agents`

**Response**:
```json
{
  "agents": [
    {
      "id": "example-task",
      "name": "Example Task Agent",
      "enabled": true,
      "category": "demo"
    }
    // ... more agents
  ]
}
```

### Stop Agent

**Endpoint**: `POST /stop/:runId`

**Response**:
```json
{
  "success": true,
  "message": "Agent stopped"
}
```

### Health Check

**Endpoint**: `GET /health`

**Response**:
```json
{
  "status": "ok",
  "uptime": 12345,
  "activeAgents": 2
}
```

---

## 🛠️ Creating Custom Agents

### Agent Template Structure

Each agent requires two files:

1. **`agent.js`** - The agent implementation
2. **`config.json`** - Agent metadata and configuration

### Directory Structure

```
agents/
└── your-agent-name/
    ├── agent.js
    └── config.json
```

### agent.js Template

```javascript
// agents/your-agent-name/agent.js

// Listen for messages from the supervisor
process.on('message', async (message) => {
  try {
    // Send initial status
    process.send({
      status: 'running',
      progress: 0,
      message: 'Starting task...'
    });

    // Get parameters
    const { param1, param2 } = message.params || {};

    // Perform your agent's work here
    // ...

    // Send progress updates
    process.send({
      status: 'running',
      progress: 50,
      message: 'Half done...'
    });

    // Send completion
    process.send({
      status: 'complete',
      progress: 100,
      result: {
        // Your results here
        success: true,
        data: 'result data'
      }
    });

    process.exit(0);
  } catch (error) {
    // Send error
    process.send({
      status: 'error',
      error: error.message,
      stack: error.stack
    });
    process.exit(1);
  }
});

// Handle termination
process.on('SIGTERM', () => {
  process.send({
    status: 'stopped',
    message: 'Agent terminated'
  });
  process.exit(0);
});
```

### config.json Template

```json
{
  "id": "your-agent-name",
  "name": "Your Agent Name",
  "version": "1.0.0",
  "description": "Brief description of what your agent does",
  "capabilities": [
    "capability-1",
    "capability-2"
  ],
  "requiresAutonomy": false,
  "defaultTimeout": 60000,
  "parameters": {
    "param1": {
      "type": "string",
      "description": "Parameter description",
      "required": true
    },
    "param2": {
      "type": "number",
      "description": "Optional parameter",
      "required": false,
      "default": 10
    }
  },
  "examples": [
    {
      "description": "Example usage",
      "params": {
        "param1": "value1",
        "param2": 20
      }
    }
  ]
}
```

### Register Your Agent

Add your agent to `registry.json`:

```json
{
  "agents": [
    // ... existing agents ...
    {
      "id": "your-agent-name",
      "name": "Your Agent Name",
      "version": "1.0.0",
      "description": "Brief description",
      "path": "./agents/your-agent-name/agent.js",
      "configPath": "./agents/your-agent-name/config.json",
      "capabilities": ["capability-1", "capability-2"],
      "requiresAutonomy": false,
      "defaultTimeout": 60000,
      "dockerImage": null,
      "enabled": true,
      "category": "your-category"
    }
  ]
}
```

### IPC Protocol

Agents communicate with the supervisor via `process.send()` messages:

**Message Format**:
```javascript
process.send({
  status: 'running' | 'complete' | 'error' | 'stopped',
  progress: 0-100,  // Optional: progress percentage
  message: 'Status message',  // Optional: status description
  result: { ... },  // Optional: results (on complete)
  error: 'Error message',  // Optional: error message (on error)
  stack: 'Error stack'  // Optional: error stack trace
});
```

---

## 🔒 Security & Safety

### Autonomy Modes

**Default Mode** (`ALLOW_AUTONOMY=false`):
- Read-only operations
- No file modifications
- No destructive actions
- Safe for testing

**Autonomous Mode** (`ALLOW_AUTONOMY=true`):
- Controlled write access
- File modifications allowed
- Subject to manifest restrictions
- Required for production agents

### Manifest Restrictions

The `manifest.json` defines security boundaries:

**Allowed Directories**:
- `/root/Zpanel/agents/mcp-supervisor/workspace/`
- `/root/Zpanel/agents/mcp-supervisor/backups/`
- `/tmp/`

**Blocked Commands**:
- `sudo`
- `rm -rf /`
- `dd`
- `mkfs`
- `shutdown`
- `reboot`

**Protected Paths**:
- `/etc/passwd`
- `/etc/shadow`
- `/root/.ssh/`
- `~/.aws/`

### Resource Limits

- **Concurrent Agents**: 10 maximum
- **Memory**: 512MB per agent
- **CPU**: 1 core per agent
- **Timeout**: Configurable per agent (default: 60-180 seconds)

---

## 🚀 Quick Start

### Start the Supervisor

```bash
cd /root/Zpanel/agents/mcp-supervisor
npm start
```

Or using systemd:
```bash
sudo systemctl start mcp-supervisor
sudo systemctl status mcp-supervisor
```

### Test with Example Agent

```bash
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "example-task",
    "params": {}
  }'
```

### Use Helper Scripts

The supervisor includes helper scripts for common operations:

```bash
# Source helper functions
source /root/Zpanel/agents/mcp-supervisor/mcp-functions.sh

# Run an agent
mcp_run_agent "health-checker" '{"checks":["all"]}'

# List all agents
mcp_list_agents

# Check supervisor health
mcp_health
```

---

## 📝 Usage Examples

### Example 1: System Health Check

```bash
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "health-checker",
    "params": {
      "checks": ["cpu", "memory", "disk"]
    }
  }'
```

### Example 2: Analyze Logs

```bash
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "log-analyzer",
    "params": {
      "logPath": "/var/log/syslog",
      "operation": "errors"
    }
  }'
```

### Example 3: Data Conversion

```bash
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "data-transformer",
    "params": {
      "inputPath": "/path/to/input.json",
      "outputPath": "/path/to/output.csv",
      "inputFormat": "json",
      "outputFormat": "csv"
    }
  }'
```

### Example 4: Create Backup

```bash
curl -X POST http://localhost:3001/run-agent \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": "backup-manager",
    "params": {
      "operation": "create",
      "sourcePath": "/important/data",
      "backupDir": "/backups",
      "compress": true
    }
  }'
```

---

## 🔧 Configuration

### Environment Variables

Create a `.env` file in the supervisor directory:

```bash
# Allow autonomous operations
ALLOW_AUTONOMY=true

# Port configuration
PORT=3001

# Logging
LOG_LEVEL=info

# Docker support
USE_DOCKER=false
```

### Manifest Configuration

Edit `manifest.json` to customize security settings:

```json
{
  "allowedDirectories": [
    "/root/Zpanel/agents/mcp-supervisor/workspace/",
    "/custom/allowed/path/"
  ],
  "blockedCommands": [
    "sudo",
    "rm -rf /"
  ],
  "resourceLimits": {
    "maxConcurrentAgents": 10,
    "maxMemoryPerAgent": "512m",
    "maxCpuPerAgent": 1
  }
}
```

---

## 📊 Monitoring & Logs

### View Logs

```bash
# Follow live logs
tail -f /root/Zpanel/agents/mcp-supervisor/logs/actions.log

# Search for errors
grep ERROR /root/Zpanel/agents/mcp-supervisor/logs/actions.log

# View recent activity
tail -n 100 /root/Zpanel/agents/mcp-supervisor/logs/actions.log
```

### Log Format

```
[2025-11-03T12:00:00.000Z] [INFO] [health-checker] Agent started
[2025-11-03T12:00:01.000Z] [INFO] [health-checker] Progress: 50%
[2025-11-03T12:00:02.000Z] [INFO] [health-checker] Agent completed successfully
```

### Monitor Resource Usage

```bash
# Check supervisor status
curl http://localhost:3001/health

# List active agents
curl http://localhost:3001/agents/active

# View agent metrics
curl http://localhost:3001/metrics
```

---

## 🐛 Troubleshooting

### Agent Not Starting

1. Check if supervisor is running:
   ```bash
   curl http://localhost:3001/health
   ```

2. Verify agent is enabled in `registry.json`:
   ```bash
   cat registry.json | jq '.agents[] | select(.id=="agent-id")'
   ```

3. Check logs for errors:
   ```bash
   tail -n 50 logs/actions.log | grep ERROR
   ```

### Agent Timeout

- Increase timeout in agent's `config.json`
- Check for infinite loops or blocking operations
- Verify resource availability (CPU, memory)

### Permission Denied

- Enable autonomy: `ALLOW_AUTONOMY=true`
- Verify path is in `manifest.json` allowed directories
- Check file permissions

### Agent Returns Error

1. Check error in response:
   ```bash
   curl http://localhost:3001/status/RUN_ID
   ```

2. Review agent logs:
   ```bash
   grep "agent-id" logs/actions.log
   ```

3. Test agent directly:
   ```bash
   node agents/agent-id/agent.js
   ```

---

## 📚 Additional Resources

### Documentation

- [README.md](agents/mcp-supervisor/README.md) - Complete supervisor guide
- [CONTRIBUTING.md](agents/mcp-supervisor/CONTRIBUTING.md) - Contribution guidelines
- [DEPLOYMENT.md](agents/mcp-supervisor/DEPLOYMENT.md) - Deployment instructions
- [QUICKSTART.md](agents/mcp-supervisor/QUICKSTART.md) - Quick start guide

### Helper Scripts

- `mcp-helper.sh` - CLI tool for agent operations
- `mcp-functions.sh` - Shell functions for scripting
- `setup.sh` - Initial setup script

### Support

- **Project**: Zpanel
- **Repository**: https://github.com/freqkflag/mcp-supervisor
- **Issues**: Report bugs and feature requests via GitHub Issues

---

## 📄 License

MIT License - See LICENSE file for details

---

**Last Updated**: November 3, 2025  
**Maintained by**: Zpanel Team

