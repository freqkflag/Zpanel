# MCP Server Framework Enhancement

## Overview

Enhancing the existing MCP integration in Coolify (Laravel Boost) to support multiple MCP servers including Cloudflare, GitHub, and custom servers.

## Current State

Coolify already has `.mcp.json` configuration:
```json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "php",
            "args": ["artisan", "boost:mcp"]
        }
    }
}
```

## Implementation Steps

### Step 1: Review Existing Implementation

First, review the Laravel Boost MCP implementation:
```bash
# Find Laravel Boost MCP files
find . -path "*/vendor/laravel/boost*" -name "*mcp*"
find . -path "*/app/*" -name "*boost*" -o -name "*mcp*"
```

### Step 2: Create MCP Server Registry

**File**: `app/Services/MCP/ServerRegistry.php`

```php
<?php

namespace App\Services\MCP;

use App\Models\MCPServer;
use Illuminate\Support\Facades\Log;

class ServerRegistry
{
    /**
     * Register a new MCP server
     */
    public function register(string $name, array $config): MCPServer
    {
        return MCPServer::create([
            'name' => $name,
            'type' => $config['type'] ?? 'custom',
            'config' => $config,
            'status' => 'active',
        ]);
    }
    
    /**
     * Get MCP server by name
     */
    public function getServer(string $name): ?MCPServer
    {
        return MCPServer::where('name', $name)
            ->where('status', 'active')
            ->first();
    }
    
    /**
     * List all active MCP servers
     */
    public function listServers(): array
    {
        return MCPServer::where('status', 'active')
            ->get()
            ->map(function ($server) {
                return [
                    'name' => $server->name,
                    'type' => $server->type,
                    'status' => $server->status,
                    'config' => $server->config,
                ];
            })
            ->toArray();
    }
    
    /**
     * Update server status
     */
    public function updateStatus(string $name, string $status): bool
    {
        $server = $this->getServer($name);
        if (!$server) {
            return false;
        }
        
        $server->update(['status' => $status]);
        return true;
    }
    
    /**
     * Health check for MCP server
     */
    public function healthCheck(string $name): array
    {
        $server = $this->getServer($name);
        if (!$server) {
            return ['status' => 'not_found'];
        }
        
        // TODO: Implement actual health check based on server type
        return [
            'status' => 'healthy',
            'name' => $server->name,
            'checked_at' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Generate .mcp.json configuration
     */
    public function generateConfig(): array
    {
        $servers = MCPServer::where('status', 'active')->get();
        
        $mcpConfig = ['mcpServers' => []];
        
        foreach ($servers as $server) {
            $mcpConfig['mcpServers'][$server->name] = $server->config;
        }
        
        return $mcpConfig;
    }
}
```

### Step 3: Create MCP Server Model

**File**: `app/Models/MCPServer.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MCPServer extends Model
{
    protected $fillable = [
        'name',
        'type',
        'config',
        'status',
    ];
    
    protected $casts = [
        'config' => 'array',
    ];
    
    /**
     * Server types
     */
    public const TYPE_CLOUDFLARE = 'cloudflare';
    public const TYPE_GITHUB = 'github';
    public const TYPE_DATABASE = 'database';
    public const TYPE_DOCKER = 'docker';
    public const TYPE_CUSTOM = 'custom';
    
    /**
     * Server statuses
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ERROR = 'error';
}
```

### Step 4: Database Migration

**File**: `database/migrations/xxxx_create_mcp_servers_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mcp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->default('custom');
            $table->json('config');
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->text('last_error')->nullable();
            $table->timestamp('last_health_check')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('type');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('mcp_servers');
    }
};
```

### Step 5: Create MCP Server Controller

**File**: `app/Http/Controllers/MCPServerController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\MCPServer;
use App\Services\MCP\ServerRegistry;
use Illuminate\Http\Request;

class MCPServerController extends Controller
{
    private ServerRegistry $registry;
    
    public function __construct(ServerRegistry $registry)
    {
        $this->registry = $registry;
        $this->middleware('auth');
    }
    
    /**
     * List all MCP servers
     */
    public function index()
    {
        $servers = MCPServer::all();
        
        return view('mcp.index', [
            'servers' => $servers,
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        return view('mcp.create');
    }
    
    /**
     * Store new MCP server
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:mcp_servers,name',
            'type' => 'required|string|in:cloudflare,github,database,docker,custom',
            'config' => 'required|array',
        ]);
        
        $server = $this->registry->register(
            $validated['name'],
            array_merge($validated['config'], ['type' => $validated['type']])
        );
        
        return redirect()->route('mcp.index')
            ->with('success', 'MCP Server created successfully');
    }
    
    /**
     * Show edit form
     */
    public function edit(MCPServer $mcpServer)
    {
        return view('mcp.edit', [
            'server' => $mcpServer,
        ]);
    }
    
    /**
     * Update MCP server
     */
    public function update(Request $request, MCPServer $mcpServer)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:mcp_servers,name,' . $mcpServer->id,
            'type' => 'required|string',
            'config' => 'required|array',
            'status' => 'required|string|in:active,inactive,error',
        ]);
        
        $mcpServer->update($validated);
        
        return redirect()->route('mcp.index')
            ->with('success', 'MCP Server updated successfully');
    }
    
    /**
     * Delete MCP server
     */
    public function destroy(MCPServer $mcpServer)
    {
        $mcpServer->delete();
        
        return redirect()->route('mcp.index')
            ->with('success', 'MCP Server deleted successfully');
    }
    
    /**
     * Health check
     */
    public function healthCheck(MCPServer $mcpServer)
    {
        $health = $this->registry->healthCheck($mcpServer->name);
        
        return response()->json($health);
    }
    
    /**
     * Get MCP configuration JSON
     */
    public function config()
    {
        $config = $this->registry->generateConfig();
        
        return response()->json($config);
    }
}
```

### Step 6: Add Routes

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->prefix('mcp')->name('mcp.')->group(function () {
    Route::get('/', [App\Http\Controllers\MCPServerController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\MCPServerController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\MCPServerController::class, 'store'])->name('store');
    Route::get('/{mcpServer}/edit', [App\Http\Controllers\MCPServerController::class, 'edit'])->name('edit');
    Route::put('/{mcpServer}', [App\Http\Controllers\MCPServerController::class, 'update'])->name('update');
    Route::delete('/{mcpServer}', [App\Http\Controllers\MCPServerController::class, 'destroy'])->name('destroy');
    Route::get('/{mcpServer}/health', [App\Http\Controllers\MCPServerController::class, 'healthCheck'])->name('health');
    Route::get('/config', [App\Http\Controllers\MCPServerController::class, 'config'])->name('config');
});
```

### Step 7: Create Views

**File**: `resources/views/mcp/index.blade.php`
**File**: `resources/views/mcp/create.blade.php`
**File**: `resources/views/mcp/edit.blade.php`

(Detailed view implementations can be created based on your UI framework)

## Integration with Cloudflare MCP Server

See `Cloudflare-MCP-integration.md` for Cloudflare-specific MCP server implementation.

## Next Steps

1. Implement health check logic for each server type
2. Add server monitoring and alerts
3. Create UI for server management
4. Implement server log viewing
5. Add server metrics/analytics

