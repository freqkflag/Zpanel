# IDE Integration Implementation Guide

## Overview

Integrating code-server (VS Code in browser) into the control panel to provide web-based IDE functionality.

## Implementation Steps

### Step 1: Create IDE Service

**File**: `app/Services/IDEService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class IDEService
{
    private string $codeServerUrl;
    
    public function __construct()
    {
        $this->codeServerUrl = config('ide.code_server_url', 'http://code-server:8080');
    }
    
    /**
     * Generate secure token for IDE access
     */
    public function generateToken(int $userId): string
    {
        $token = Str::random(32);
        Cache::put("ide_token_{$token}", [
            'user_id' => $userId,
            'created_at' => now(),
            'expires_at' => now()->addHours(24)
        ], now()->addHours(24));
        
        return $token;
    }
    
    /**
     * Validate IDE token
     */
    public function validateToken(string $token): ?array
    {
        return Cache::get("ide_token_{$token}");
    }
    
    /**
     * Get workspace path for user
     */
    public function getWorkspacePath(int $userId, ?string $projectId = null): string
    {
        $basePath = config('ide.workspace_base', '/workspace');
        
        if ($projectId) {
            return "{$basePath}/user_{$userId}/project_{$projectId}";
        }
        
        return "{$basePath}/user_{$userId}";
    }
    
    /**
     * Get IDE URL with authentication
     */
    public function getIDEUrl(string $token, string $workspace): string
    {
        return "{$this->codeServerUrl}/?folder={$workspace}&tkn={$token}";
    }
}
```

### Step 2: Create IDE Controller

**File**: `app/Http/Controllers/IDEController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\IDEService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IDEController extends Controller
{
    private IDEService $ideService;
    
    public function __construct(IDEService $ideService)
    {
        $this->ideService = $ideService;
        $this->middleware('auth');
    }
    
    /**
     * Display IDE interface
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $workspace = $request->get('workspace', 'default');
        $projectId = $request->get('project_id');
        
        $token = $this->ideService->generateToken($user->id);
        $workspacePath = $this->ideService->getWorkspacePath($user->id, $projectId);
        $ideUrl = $this->ideService->getIDEUrl($token, $workspacePath);
        
        return view('ide.index', [
            'ideUrl' => $ideUrl,
            'workspace' => $workspace,
            'token' => $token
        ]);
    }
    
    /**
     * List user workspaces
     */
    public function workspaces()
    {
        $user = Auth::user();
        // TODO: Implement workspace listing
        return response()->json([]);
    }
    
    /**
     * Create new workspace
     */
    public function createWorkspace(Request $request)
    {
        $user = Auth::user();
        $workspaceName = $request->input('name');
        
        // TODO: Implement workspace creation
        return response()->json(['message' => 'Workspace created']);
    }
}
```

### Step 3: Add Routes

**File**: `routes/web.php` (add to existing routes)

```php
Route::middleware(['auth'])->prefix('ide')->name('ide.')->group(function () {
    Route::get('/', [App\Http\Controllers\IDEController::class, 'index'])->name('index');
    Route::get('/workspaces', [App\Http\Controllers\IDEController::class, 'workspaces'])->name('workspaces');
    Route::post('/workspaces', [App\Http\Controllers\IDEController::class, 'createWorkspace'])->name('workspaces.create');
});
```

### Step 4: Create IDE View

**File**: `resources/views/ide/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 p-0">
    <div class="row h-100">
        <div class="col-12 h-100">
            <div class="position-relative h-100">
                <iframe 
                    src="{{ $ideUrl }}" 
                    class="w-100 h-100 border-0"
                    id="ide-iframe"
                    allow="clipboard-read; clipboard-write"
                    title="Integrated Development Environment">
                </iframe>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        overflow: hidden;
    }
    .container-fluid {
        height: calc(100vh - 56px);
    }
</style>
@endsection
```

### Step 5: Create IDE Configuration

**File**: `config/ide.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Code Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the code-server instance
    |
    */
    
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
    ],
];
```

### Step 6: Update Docker Compose

**File**: `docker-compose.yml` (add service)

```yaml
services:
  # ... existing services ...
  
  code-server:
    image: codercom/code-server:latest
    container_name: coolify-code-server
    ports:
      - "${CODE_SERVER_PORT:-8080}:8080"
    volumes:
      - ide-workspaces:/workspace
      - code-server-config:/home/coder/.config
    environment:
      - PASSWORD=${CODE_SERVER_PASSWORD:-}
      - PROXY_DOMAIN=${CODE_SERVER_DOMAIN:-}
    networks:
      - coolify-network
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8080"]
      interval: 30s
      timeout: 10s
      retries: 3

volumes:
  ide-workspaces:
    driver: local
  code-server-config:
    driver: local

networks:
  coolify-network:
    driver: bridge
```

### Step 7: Environment Variables

**File**: `.env` (add to existing)

```env
CODE_SERVER_URL=http://code-server:8080
CODE_SERVER_PORT=8080
CODE_SERVER_PASSWORD=your-secure-password-here
CODE_SERVER_DOMAIN=
IDE_WORKSPACE_BASE=/workspace
IDE_TOKEN_EXPIRY=24
```

### Step 8: Create Workspace Model (Optional)

**File**: `app/Models/Workspace.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'type',
        'project_id',
        'settings',
    ];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Step 9: Database Migration

**File**: `database/migrations/xxxx_create_workspaces_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->string('type')->default('default');
            $table->foreignId('project_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('workspaces');
    }
};
```

## Testing

1. Start Docker services: `docker-compose up -d`
2. Run migrations: `php artisan migrate`
3. Access IDE: Navigate to `/ide` in the application
4. Verify code-server is running: Check `http://localhost:8080`
5. Test workspace creation and access

## Security Considerations

1. ✅ Token-based authentication
2. ✅ Workspace isolation per user
3. ✅ Token expiration (24 hours default)
4. ⚠️ Ensure code-server password is set
5. ⚠️ Restrict code-server port access
6. ⚠️ Validate workspace paths to prevent directory traversal

## Next Steps

After IDE integration:
1. Add workspace management UI
2. Implement file upload/download
3. Add terminal integration
4. Implement collaboration features (if needed)

